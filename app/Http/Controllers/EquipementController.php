<?php
// Contrôleur : EquipementController
// CRUD des équipements réseau (personnel uniquement) + gestion des interfaces du BACKBONE.
// Couvre : Associer un équipement à une technologie / Gérer les interfaces de l'équipement backbone
// (côté international : neighbor + AS local — côté accès : équipement d'accès + technologie).

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ConfigurationAs;
use App\Models\Equipement;
use App\Models\InterfaceReseau;
use App\Models\JournalAudit;
use App\Models\Operateur;
use App\Models\Technologie;
use Illuminate\Http\Request;

class EquipementController extends Controller
{
    /**
     * Liste des équipements avec leurs relations (technologie, client) et le nombre d'interfaces.
     */
    public function index()
    {
        $equipements = Equipement::with(['technologie', 'client'])  // Chargement anticipé (évite le N+1)
            ->withCount('interfaces')                               // Compteur d'interfaces (backbone)
            ->orderByDesc('role')                                   // Le backbone d'abord, puis l'accès
            ->orderBy('nom')
            ->paginate(15);
        return view('equipements.index', compact('equipements'));
    }

    /**
     * Formulaire d'ajout d'un équipement.
     */
    public function create()
    {
        return view('equipements.create', [
            'technologies' => Technologie::orderBy('nom')->get(),   // Liste déroulante des technologies
            'clients'      => Client::orderBy('nom')->get(),        // Liste déroulante des clients
        ]);
    }

    /**
     * Enregistre un nouvel équipement.
     */
    public function store(Request $request)
    {
        $donnees = $this->valider($request);                // Règles factorisées dans une méthode privée
        $equipement = Equipement::create($donnees);         // Insertion en base

        // [AUDIT] Trace la création de l'équipement.
        JournalAudit::consigner('creation', 'Equipement', $equipement->id, "Création de l'équipement « {$equipement->nom} ».");

        return redirect()->route('equipements.index')->with('succes', 'Équipement ajouté.');
    }

    /**
     * Fiche détaillée d'un équipement. Pour le BACKBONE (cadre "Local" du schéma), elle expose
     * ses interfaces en deux volets : côté international (relations externes) et côté accès.
     */
    public function show(Equipement $equipement)
    {
        // Relations utiles à la fiche, chargées en une passe.
        $equipement->load([
            'technologie', 'client',
            'interfacesInternationales.operateur', 'interfacesInternationales.sessionsBgp',
            'interfacesAcces.equipementDistant', 'interfacesAcces.technologie',
        ]);

        return view('equipements.show', [
            'equipement'      => $equipement,
            // [CÔTÉ INTERNATIONAL] L'AS local affiché sur chaque relation externe vient des paramètres AS.
            'configurationAs' => ConfigurationAs::courante(),
            // Listes des formulaires d'ajout d'interface.
            'operateurs'      => Operateur::orderBy('nom')->get(),          // Neighbors possibles
            'equipementsAcces'=> Equipement::where('role', 'acces')         // Équipements d'accès raccordables
                                    ->where('id', '!=', $equipement->id)->orderBy('nom')->get(),
            'technologies'    => Technologie::orderBy('nom')->get(),        // Technologies d'accès
        ]);
    }

    /**
     * Formulaire de modification d'un équipement.
     */
    public function edit(Equipement $equipement)
    {
        return view('equipements.edit', [
            'equipement'   => $equipement,                          // L'équipement à modifier
            'technologies' => Technologie::orderBy('nom')->get(),   // Choix de la technologie
            'clients'      => Client::orderBy('nom')->get(),        // Choix du client
        ]);
    }

    /**
     * Met à jour un équipement.
     */
    public function update(Request $request, Equipement $equipement)
    {
        $equipement->update($this->valider($request));      // Validation + mise à jour

        // [AUDIT] Trace la modification de l'équipement.
        JournalAudit::consigner('modification', 'Equipement', $equipement->id, "Modification de l'équipement « {$equipement->nom} ».");

        return redirect()->route('equipements.index')->with('succes', 'Équipement modifié.');
    }

    /**
     * Supprime un équipement (ses interfaces sont supprimées en cascade).
     */
    public function destroy(Equipement $equipement)
    {
        // [AUDIT] Trace la suppression AVANT l'opération.
        JournalAudit::consigner('suppression', 'Equipement', $equipement->id, "Suppression de l'équipement « {$equipement->nom} ».");

        $equipement->delete();                              // Suppression en base
        return redirect()->route('equipements.index')->with('succes', 'Équipement supprimé.');
    }

    /**
     * [BACKBONE] Ajoute une interface à l'équipement depuis sa fiche détaillée.
     * Selon le type : "internationale" => neighbor obligatoire ; "acces" => équipement/technologie derrière.
     */
    public function ajouterInterface(Request $request, Equipement $equipement)
    {
        // Seul l'équipement backbone porte des interfaces (cadre "Local" du schéma).
        abort_unless($equipement->estBackbone(), 404);

        $donnees = $request->validate([
            'nom'                   => ['required', 'string', 'max:255'],       // Nom de l'interface
            'adresse_ip'            => ['nullable', 'ip'],                      // IP portée par l'interface
            'bande_passante'        => ['nullable', 'integer', 'min:1'],        // Débit du lien (Mbps)
            'type'                  => ['required', 'in:internationale,acces'], // Côté de l'interface
            // Côté international : le neighbor (opérateur) est obligatoire.
            'operateur_id'          => ['required_if:type,internationale', 'nullable', 'exists:operateurs,id'],
            // Côté accès : équipement d'accès et technologie desservie derrière l'interface.
            'equipement_distant_id' => ['required_if:type,acces', 'nullable', 'exists:equipements,id'],
            'technologie_id'        => ['required_if:type,acces', 'nullable', 'exists:technologies,id'],
        ]);

        $interface = $equipement->interfaces()->create($donnees);   // Création rattachée au backbone

        // [AUDIT] Trace l'ajout de l'interface.
        JournalAudit::consigner('modification', 'Equipement', $equipement->id,
            "Ajout de l'interface « {$interface->nom} » ({$interface->type}) sur « {$equipement->nom} ».");

        return back()->with('succes', 'Interface ajoutée.');        // Retour sur la fiche
    }

    /**
     * [BACKBONE] Retire une interface de l'équipement.
     */
    public function supprimerInterface(Equipement $equipement, InterfaceReseau $interface)
    {
        // Sécurité : l'interface doit bien appartenir à l'équipement de l'URL.
        abort_unless($interface->equipement_id === $equipement->id, 404);

        // [AUDIT] Trace la suppression AVANT l'opération.
        JournalAudit::consigner('modification', 'Equipement', $equipement->id,
            "Suppression de l'interface « {$interface->nom} » ({$interface->type}) de « {$equipement->nom} ».");

        $interface->delete();                               // Les sessions BGP liées passent à interface nulle
        return back()->with('succes', 'Interface supprimée.');
    }

    /**
     * Règles de validation communes à la création et à la modification.
     */
    private function valider(Request $request): array
    {
        return $request->validate([
            'nom'            => ['required', 'string', 'max:255'],          // Nom obligatoire
            'type'           => ['required', 'string', 'max:255'],          // Type obligatoire
            'role'           => ['required', 'in:backbone,acces'],          // Rôle dans l'architecture
            'adresse_ip'     => ['nullable', 'ip'],                         // IP de management valide
            'technologie_id' => ['nullable', 'exists:technologies,id'],     // Technologie existante
            'client_id'      => ['nullable', 'exists:clients,id'],          // Client existant
        ]);
    }
}
