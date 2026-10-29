<?php
// Contrôleur : SessionBgpController
// CRUD des sessions BGP + consultation des préfixes reçus/annoncés (personnel uniquement).
// Couvre : Visualiser les sessions actives / Ajouter / Modifier / Supprimer une session /
//          Consulter les préfixes reçus et annoncés / Associer un opérateur à une session.

namespace App\Http\Controllers;

use App\Models\ConfigurationAs;
use App\Models\InterfaceReseau;
use App\Models\JournalAudit;
use App\Models\Operateur;
use App\Models\PlageIp;                    // Réutilisé pour la validation CIDR IPv4/IPv6
use App\Models\PrefixeBgp;
use App\Models\SessionBgp;
use Illuminate\Http\Request;

class SessionBgpController extends Controller
{
    /**
     * Liste des sessions BGP (cas "Visualiser les sessions BGP actives").
     * Un filtre optionnel ?etat=active permet de n'afficher que les sessions établies.
     */
    public function index(Request $request)
    {
        // Charge le neighbor (opérateur) et l'interface locale associés (évite le N+1)
        $requete = SessionBgp::with(['operateur', 'interfaceLocale']);

        // Si le filtre d'état est fourni et valide, on l'applique à la requête SQL.
        if (in_array($request->query('etat'), ['active', 'inactive'])) {
            $requete->where('etat', $request->query('etat'));
        }

        $sessions = $requete->orderByDesc('etat')->paginate(15);   // Sessions actives en premier
        return view('sessions_bgp.index', [
            'sessions'        => $sessions,
            // ASN LOCAL affiché sur chaque session : paramètre global de l'AS
            'configurationAs' => ConfigurationAs::courante(),
        ]);
    }

    /**
     * Formulaire d'ajout d'une session BGP (cas "Ajouter une session BGP").
     */
    public function create()
    {
        return view('sessions_bgp.create', $this->donneesFormulaire());
    }

    /**
     * Enregistre une nouvelle session BGP.
     */
    public function store(Request $request)
    {
        $donnees = $this->validerSession($request);         // Règles factorisées (voir méthode privée)

        $session = SessionBgp::create($donnees);            // Insertion en base

        // [AUDIT] Trace la création de la session BGP.
        JournalAudit::consigner('creation', 'SessionBgp', $session->id, "Création de la session BGP vers {$session->ip_voisin}.");

        return redirect()->route('sessions-bgp.index')->with('succes', 'Session BGP ajoutée.');
    }

    /**
     * Fiche détaillée d'une session : affiche les préfixes reçus et annoncés
     * (cas "Consulter les préfixes reçus et annoncés").
     */
    public function show(SessionBgp $sessions_bgp)
    {
        // Chargement des relations nécessaires à la vue en une seule passe.
        $sessions_bgp->load(['operateur', 'interfaceLocale.equipement', 'prefixesRecus', 'prefixesAnnonces']);
        return view('sessions_bgp.show', [
            'session'         => $sessions_bgp,
            'configurationAs' => ConfigurationAs::courante(),   // ASN local affiché sur la fiche
        ]);
    }

    /**
     * Formulaire de modification (cas "Modifier une session BGP").
     */
    public function edit(SessionBgp $sessions_bgp)
    {
        return view('sessions_bgp.edit', ['session' => $sessions_bgp] + $this->donneesFormulaire());
    }

    /**
     * Met à jour une session BGP existante.
     */
    public function update(Request $request, SessionBgp $sessions_bgp)
    {
        $donnees = $this->validerSession($request);         // Mêmes règles qu'à la création

        $sessions_bgp->update($donnees);                    // Mise à jour en base

        // [AUDIT] Trace la modification de la session BGP.
        JournalAudit::consigner('modification', 'SessionBgp', $sessions_bgp->id, "Modification de la session BGP vers {$sessions_bgp->ip_voisin}.");

        return redirect()->route('sessions-bgp.index')->with('succes', 'Session BGP modifiée.');
    }

    /**
     * Supprime une session BGP (cas "Supprimer une session BGP").
     * Ses préfixes sont supprimés en cascade.
     */
    public function destroy(SessionBgp $sessions_bgp)
    {
        // [AUDIT] Trace la suppression AVANT l'opération.
        JournalAudit::consigner('suppression', 'SessionBgp', $sessions_bgp->id, "Suppression de la session BGP vers {$sessions_bgp->ip_voisin}.");

        $sessions_bgp->delete();                            // Suppression en base
        return redirect()->route('sessions-bgp.index')->with('succes', 'Session BGP supprimée.');
    }

    /**
     * Ajoute un préfixe (reçu ou annoncé) à une session depuis sa fiche détaillée.
     */
    public function ajouterPrefixe(Request $request, SessionBgp $sessions_bgp)
    {
        // [IPv4/IPv6] Validation : bloc CIDR valide dans l'une des deux versions
        // (ex : 10.0.0.0/8 OU 2001:db8::/32), via l'analyseur partagé du modèle PlageIp.
        $donnees = $request->validate([
            'prefixe' => ['required',
                function ($attribut, $valeur, $echec) {
                    if (PlageIp::analyserCidr($valeur) === null) {  // Ni IPv4 ni IPv6 valide ?
                        $echec('Le préfixe doit être un CIDR IPv4 ou IPv6 valide.');
                    }
                },
            ],
            'type'    => ['required', 'in:recu,annonce'],   // Sens du préfixe
        ]);

        // Création du préfixe rattaché à la session courante.
        $prefixe = $sessions_bgp->prefixes()->create($donnees);

        // [AUDIT] Trace l'ajout du préfixe.
        JournalAudit::consigner('modification', 'SessionBgp', $sessions_bgp->id,
            "Ajout du préfixe « {$prefixe->prefixe} » ({$prefixe->type}) sur la session BGP vers {$sessions_bgp->ip_voisin}.");

        return back()->with('succes', 'Préfixe ajouté.');   // Retour sur la fiche de la session
    }

    /**
     * Retire un préfixe d'une session.
     */
    public function supprimerPrefixe(SessionBgp $sessions_bgp, PrefixeBgp $prefixe)
    {
        // Sécurité : on vérifie que le préfixe appartient bien à la session de l'URL.
        abort_unless($prefixe->session_bgp_id === $sessions_bgp->id, 404);

        // [AUDIT] Trace la suppression AVANT l'opération.
        JournalAudit::consigner('modification', 'SessionBgp', $sessions_bgp->id,
            "Suppression du préfixe « {$prefixe->prefixe} » ({$prefixe->type}) de la session BGP vers {$sessions_bgp->ip_voisin}.");

        $prefixe->delete();                                 // Suppression du préfixe
        return back()->with('succes', 'Préfixe supprimé.');
    }

    /**
     * Règles de validation communes (création + modification) — champs du schéma :
     * neighbor (opérateur), adresse neighbor, ASN, adresse locale, statut ;
     * l'ASN local vient de la configuration de l'AS (jamais saisi ici).
     */
    private function validerSession(Request $request): array
    {
        $donnees = $request->validate([
            'operateur_id'    => ['required', 'exists:operateurs,id'], // NEIGHBOR : opérateur obligatoire
            'ip_voisin'       => ['required', 'ip'],                   // Adresse NEIGHBOR (IPv4 ou IPv6)
            'asn_distant'     => ['required', 'integer', 'min:1'],     // ASN du neighbor
            'ip_locale'       => ['nullable', 'ip'],                   // Adresse LOCALE (notre côté du lien)
            // Interface internationale du backbone empruntée par la session (facultative)
            'interface_id'    => ['nullable', 'exists:interfaces,id'],
            'etat'            => ['required', 'in:active,inactive'],   // STATUT limité aux deux valeurs
            // [CAPACITY PLANNING] Débit physique global fourni par l'opérateur (Mbps)
            'capacite_totale' => ['nullable', 'integer', 'min:1'],
            'description'     => ['nullable', 'string'],               // Notes libres
        ]);

        // Garde-fou : l'interface choisie doit être une interface CÔTÉ INTERNATIONAL.
        if (! empty($donnees['interface_id'])) {
            $interface = InterfaceReseau::findOrFail($donnees['interface_id']);
            abort_unless($interface->estInternationale(), 422, "L'interface choisie n'est pas une interface internationale.");
            // Confort : si l'adresse locale n'est pas saisie, on reprend celle de l'interface.
            $donnees['ip_locale'] = $donnees['ip_locale'] ?: $interface->adresse_ip;
        }

        return $donnees;
    }

    /**
     * Données communes aux formulaires de création et de modification.
     */
    private function donneesFormulaire(): array
    {
        return [
            'operateurs'      => Operateur::orderBy('nom')->get(),  // Neighbors possibles
            // Interfaces internationales du backbone (avec leur équipement pour un libellé parlant)
            'interfaces'      => InterfaceReseau::with('equipement')
                                    ->where('type', 'internationale')->orderBy('nom')->get(),
            // ASN LOCAL affiché en lecture seule dans le formulaire
            'configurationAs' => ConfigurationAs::courante(),
        ];
    }
}
