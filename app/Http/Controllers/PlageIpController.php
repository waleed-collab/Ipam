<?php
// Contrôleur : PlageIpController
// CRUD des plages IP (personnel uniquement) — cœur fonctionnel de l'IPAM.
// Couvre : Ajouter / Modifier / Supprimer / Consulter les plages disponibles-utilisées /
//          Associer une plage à une technologie / Associer une plage à un client /
//          [SOUS-PLAGES] Subdiviser une plage mère et suivre le nombre exact de sous-plages
//          possibles, occupées et disponibles (norme CIDR : /N -> 2^(M-N) sous-plages /M).

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\JournalAudit;
use App\Models\PlageIp;
use App\Models\Technologie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;                          // Décompte groupé des adresses attribuées

class PlageIpController extends Controller
{
    /**
     * Liste des plages IP (cas "Consulter les plages IP disponibles / utilisées").
     * Filtrable par statut via ?statut=disponible ou ?statut=utilisee.
     */
    public function index(Request $request)
    {
        $requete = PlageIp::with(['technologie', 'client']);

        // Application du filtre de statut si présent et valide dans l'URL.
        if (in_array($request->query('statut'), ['disponible', 'utilisee'])) {
            $requete->where('statut', $request->query('statut'));
        }

        $plages = $requete->orderBy('reseau')->paginate(15);        // Tri par adresse réseau

        // [PERFORMANCE] Décompte des adresses attribuées pour TOUTE la page en UNE seule requête
        // (PostgreSQL teste l'appartenance avec l'opérateur <<= sur le type inet), au lieu d'une
        // requête par ligne. Le résultat est injecté dans chaque modèle.
        $identifiants = $plages->pluck('id');
        if ($identifiants->isNotEmpty()) {
            $comptes = DB::table('plages_ip AS p')
                ->leftJoin('equipements AS e', function ($jointure) {
                    $jointure->whereNotNull('e.adresse_ip')
                             ->whereRaw('e.adresse_ip::inet <<= p.reseau');
                })
                ->whereIn('p.id', $identifiants)
                ->groupBy('p.id')
                ->selectRaw('p.id, COUNT(e.id) AS total')
                ->pluck('total', 'p.id');

            foreach ($plages as $plage) {
                $plage->definirAdressesAttribuees((int) ($comptes[$plage->id] ?? 0));
            }
        }

        return view('plages_ip.index', compact('plages'));
    }

    /**
     * Formulaire d'ajout d'une plage IP (cas "Ajouter une plage IP").
     */
    public function create()
    {
        return view('plages_ip.create', $this->donneesFormulaire());
    }

    /**
     * Enregistre une nouvelle plage IP.
     */
    public function store(Request $request)
    {
        $donnees = $this->valider($request);                // Validation (format CIDR, unicité...)
        $plage = PlageIp::create($donnees);                 // Le statut est calculé automatiquement (modèle)

        // [AUDIT] Trace la création de la plage IP.
        JournalAudit::consigner('creation', 'PlageIp', $plage->id, "Création du sous-bloc d'adresses « {$plage->reseau} ».");

        return redirect()->route('plages-ip.index')->with('succes', "Sous-bloc d'adresses ajouté.");
    }

    /**
     * [ADRESSES] Fiche détaillée d'une plage : nombre d'adresses contenues dans le bloc
     * (convention de l'application) et informations d'affectation.
     */
    public function show(PlageIp $plages_ip)
    {
        $plages_ip->load(['technologie', 'client']);

        return view('plages_ip.show', ['plage' => $plages_ip]);
    }

    /**
     * Formulaire de modification (cas "Modifier une plage IP",
     * "Associer une plage IP à une technologie" et "Associer une plage IP à un client").
     */
    public function edit(PlageIp $plages_ip)
    {
        return view('plages_ip.edit', ['plage' => $plages_ip] + $this->donneesFormulaire());
    }

    /**
     * Met à jour une plage IP (y compris ses associations).
     */
    public function update(Request $request, PlageIp $plages_ip)
    {
        $plages_ip->update($this->valider($request, $plages_ip->id)); // Validation + mise à jour

        // [AUDIT] Trace la modification de la plage IP.
        JournalAudit::consigner('modification', 'PlageIp', $plages_ip->id, "Modification du sous-bloc d'adresses « {$plages_ip->reseau} ».");

        return redirect()->route('plages-ip.index')->with('succes', "Sous-bloc d'adresses modifié.");
    }

    /**
     * Supprime une plage IP (cas "Supprimer une plage IP").
     * Ses sous-plages éventuelles sont supprimées en cascade (contrainte de la migration).
     */
    public function destroy(PlageIp $plages_ip)
    {
        // [AUDIT] Trace la suppression AVANT l'opération.
        JournalAudit::consigner('suppression', 'PlageIp', $plages_ip->id, "Suppression du sous-bloc d'adresses « {$plages_ip->reseau} ».");

        $plages_ip->delete();                               // Suppression en base
        return redirect()->route('plages-ip.index')->with('succes', "Sous-bloc d'adresses supprimé.");
    }

    /**
     * Règles de validation communes (création + modification).
     *
     * @param  int|null  $idIgnore  Id de la plage à ignorer pour la règle d'unicité (en modification)
     */
    private function valider(Request $request, ?int $idIgnore = null): array
    {
        return $request->validate([
            // [IPv4/IPv6] Bloc CIDR valide dans l'UNE des deux versions du protocole
            // (ex : 192.168.10.0/24 OU 2001:db8:abcd::/48) + unicité en base.
            'reseau'         => [
                'required',
                // Validation par closure : PlageIp::analyserCidr() retourne 4, 6 ou null
                function ($attribut, $valeur, $echec) {
                    if (PlageIp::analyserCidr($valeur) === null) {      // Format invalide ?
                        $echec('Le bloc doit être un CIDR IPv4 (ex : 192.168.10.0/24) ou IPv6 (ex : 2001:db8::/32) valide.');
                    }
                },
                'unique:plages_ip,reseau,' . ($idIgnore ?? 'NULL'),
            ],
            'description'    => ['nullable', 'string', 'max:255'],      // Description facultative
            'technologie_id' => ['nullable', 'exists:technologies,id'], // Technologie existante
            'client_id'      => ['nullable', 'exists:clients,id'],      // Client existant
        ]);
    }

    /**
     * Données communes aux formulaires : technologies et clients.
     */
    private function donneesFormulaire(): array
    {
        return [
            'technologies' => Technologie::orderBy('nom')->get(),   // Association possible à une technologie
            'clients'      => Client::orderBy('nom')->get(),        // Affectation possible à un client
        ];
    }
}
