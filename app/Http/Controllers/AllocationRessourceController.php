<?php
// Contrôleur : AllocationRessourceController
// [CAPACITY PLANNING] CRUD des allocations de bande passante (personnel uniquement).
// Applique la RÈGLE MÉTIER :
//   R1 : le débit souscrit d'un client ne peut PAS dépasser le débit max de sa technologie.

namespace App\Http\Controllers;

use App\Models\AllocationRessource;
use App\Models\Client;
use App\Models\JournalAudit;
use App\Models\Technologie;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AllocationRessourceController extends Controller
{
    /**
     * Liste des allocations avec leurs relations (client, technologie).
     */
    public function index()
    {
        return view('allocations.index', [
            // Allocations avec relations pré-chargées (aucune requête N+1)
            'allocations' => AllocationRessource::with(['client', 'technologie'])
                                ->orderByDesc('bande_passante_souscrite')->paginate(15),
        ]);
    }

    /**
     * Formulaire de création d'une allocation.
     */
    public function create()
    {
        return $this->formulaire(null);                     // Formulaire vierge
    }

    /**
     * Enregistre une nouvelle allocation après contrôle des règles métier.
     */
    public function store(Request $request)
    {
        $donnees = $this->valider($request);                // Validation + règles R1 et R2
        $allocation = AllocationRessource::create($donnees); // Insertion en base

        // [AUDIT] Trace la création de l'allocation.
        JournalAudit::consigner('creation', 'AllocationRessource', $allocation->id,
            "Création d'une allocation de {$allocation->bande_passante_souscrite} Mbps pour le client #{$allocation->client_id}.");

        return redirect()->route('allocations.index')->with('succes', 'Allocation de bande passante créée.');
    }

    /**
     * Formulaire de modification d'une allocation existante.
     */
    public function edit(AllocationRessource $allocation)
    {
        return $this->formulaire($allocation);
    }

    /**
     * Met à jour une allocation après contrôle des règles métier.
     */
    public function update(Request $request, AllocationRessource $allocation)
    {
        // On exclut l'allocation en cours de modification du calcul de la somme (règle R2)
        $allocation->update($this->valider($request, $allocation));

        // [AUDIT] Trace la modification de l'allocation.
        JournalAudit::consigner('modification', 'AllocationRessource', $allocation->id,
            "Modification de l'allocation de {$allocation->bande_passante_souscrite} Mbps pour le client #{$allocation->client_id}.");

        return redirect()->route('allocations.index')->with('succes', 'Allocation modifiée.');
    }

    /**
     * Supprime une allocation (libère la capacité correspondante).
     */
    public function destroy(AllocationRessource $allocation)
    {
        // [AUDIT] Trace la suppression AVANT l'opération.
        JournalAudit::consigner('suppression', 'AllocationRessource', $allocation->id,
            "Suppression de l'allocation de {$allocation->bande_passante_souscrite} Mbps pour le client #{$allocation->client_id}.");

        $allocation->delete();
        return redirect()->route('allocations.index')->with('succes', 'Allocation supprimée.');
    }

    /**
     * Prépare les listes déroulantes communes aux formulaires création/modification.
     */
    private function formulaire(?AllocationRessource $allocation)
    {
        return view($allocation ? 'allocations.edit' : 'allocations.create', [
            'allocation'   => $allocation,
            'clients'      => Client::orderBy('nom')->get(),            // Choix du client
            'technologies' => Technologie::orderBy('nom')->get(),       // Choix de la technologie
        ]);
    }

    /**
     * Validation + application de la règle métier R1 (limite technique de la technologie).
     *
     * @param  AllocationRessource|null $enCours  Allocation modifiée, null en création (paramètre conservé
     *                                            pour la cohérence des appels store/update)
     */
    private function valider(Request $request, ?AllocationRessource $enCours = null): array
    {
        // ---------- Validation de forme ----------
        $donnees = $request->validate([
            'client_id'                => ['required', 'exists:clients,id'],       // Client existant
            'technologie_id'           => ['required', 'exists:technologies,id'],  // Technologie existante
            'bande_passante_souscrite' => ['required', 'integer', 'min:1'],        // Débit souscrit > 0
            'description'              => ['nullable', 'string', 'max:255'],
        ]);

        // ---------- Règle R1 : limite technique de la technologie ----------
        $technologie = Technologie::find($donnees['technologie_id']);   // Technologie choisie
        if ($technologie->debit_max_supporte !== null                   // Une limite est définie ?
            && $donnees['bande_passante_souscrite'] > $technologie->debit_max_supporte) {
            throw ValidationException::withMessages([
                'bande_passante_souscrite' =>
                    "Dépassement de la limite technique : la technologie « {$technologie->nom} » supporte au maximum {$technologie->debit_max_supporte} Mbps par client.",
            ]);
        }

        return $donnees;                                    // Données validées et conformes à la règle R1
    }
}
