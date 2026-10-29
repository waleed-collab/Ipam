<?php
// Contrôleur : TableauDeBordController
// [DASHBOARD] Tableau de bord professionnel du personnel (admin & manager) :
// cartes statistiques, graphiques interactifs, indicateurs réseau, répartition IPv4/IPv6,
// capacité & bande passante, taux d'occupation, alertes et activité récente.

namespace App\Http\Controllers;

use App\Models\AllocationRessource;
use App\Models\Client;
use App\Models\ConfigurationAs;
use App\Models\Equipement;
use App\Models\Operateur;
use App\Models\PlageIp;
use App\Models\SessionBgp;
use App\Models\Technologie;
use Illuminate\Support\Facades\DB;

class TableauDeBordController extends Controller
{
    /**
     * Calcule TOUTES les statistiques du tableau de bord.
     */
    public function index()
    {
        // ---------- Compteurs de base ----------
        $nbPlagesLibres    = PlageIp::where('statut', 'disponible')->count();   // Plages IP libres
        $nbPlagesUtilisees = PlageIp::where('statut', 'utilisee')->count();     // Plages IP affectées
        $nbPlagesTotal     = $nbPlagesLibres + $nbPlagesUtilisees;              // Total des plages

        // ---------- Répartition IPv4 / IPv6 ----------
        $ipv4 = $this->statistiquesVersion(4);              // Plages, capacité et bande passante IPv4
        $ipv6 = $this->statistiquesVersion(6);              // Idem pour IPv6

        // ---------- Capacité & bande passante (capacity planning) ----------
        $capaciteTotale  = (int) SessionBgp::sum('capacite_totale');                    // Débit physique total (Mbps)
        $bandeSouscrite  = (int) AllocationRessource::sum('bande_passante_souscrite');  // Débit total souscrit (Mbps)
        $capaciteRestante = max(0, $capaciteTotale - $bandeSouscrite);                  // Capacité encore libre
        // Taux d'occupation global de la bande passante (0 si aucune capacité définie)
        $tauxBandePassante = $capaciteTotale > 0 ? (int) round($bandeSouscrite / $capaciteTotale * 100) : 0;

        // ---------- Bande passante souscrite par technologie (graphique) ----------
        $bpParTechnologie = AllocationRessource::query()
            ->join('technologies', 'technologies.id', '=', 'allocation_ressources.technologie_id')
            ->groupBy('technologies.nom')                    // Un point par technologie
            ->orderBy('technologies.nom')
            ->select('technologies.nom', DB::raw('SUM(bande_passante_souscrite) AS total'))
            ->pluck('total', 'nom');                         // ['Fibre optique' => 120, ...]

        // ---------- Alertes réseau ----------
        $alertes = [];
        // Alerte 1 : sessions BGP inactives (lien potentiellement coupé)
        foreach (SessionBgp::with('operateur')->where('etat', 'inactive')->get() as $s) {
            $alertes[] = ['niveau' => 'rouge',
                'texte' => "Session BGP inactive vers {$s->operateur->nom} (voisin {$s->ip_voisin})."];
        }
        // Alerte 2 : bande passante souscrite globale proche de la capacité physique totale
        if ($capaciteTotale > 0 && $tauxBandePassante >= 90) {
            $alertes[] = ['niveau' => 'orange',
                'texte' => "Bande passante presque saturée ({$tauxBandePassante} %) : {$bandeSouscrite} Mbps souscrits sur {$capaciteTotale} Mbps de capacité."];
        }
        // Alerte 3 : sous-blocs d'adresses sans technologie associée (donnée incomplète)
        $sansTechno = PlageIp::whereNull('technologie_id')->count();
        if ($sansTechno > 0) {
            $alertes[] = ['niveau' => 'gris', 'texte' => "{$sansTechno} bloc(s) d'adresses sans technologie associée."];
        }

        // ---------- [ÉPUISEMENT] État de remplissage de chaque sous-bloc ----------
        // Une adresse est consommée dès qu'un équipement la porte. On classe les sous-blocs
        // du plus rempli au moins rempli : ceux qui approchent de la saturation viennent en tête.
        $sousBlocs = $this->sousBlocsAvecRemplissage();

        // Alerte 4 : sous-blocs saturés ou sur le point de l'être (seuil : 90 %)
        foreach ($sousBlocs as $sousBloc) {
            if ($sousBloc->adressesRestantes() === 0) {
                $alertes[] = ['niveau' => 'rouge',
                    'texte' => "Sous-bloc {$sousBloc->reseau} épuisé : plus aucune adresse disponible."];
            } elseif ($sousBloc->tauxRemplissage() >= 90) {
                $alertes[] = ['niveau' => 'orange',
                    'texte' => "Sous-bloc {$sousBloc->reseau} bientôt épuisé ({$sousBloc->tauxRemplissage()} %) : "
                             . "il ne reste que {$sousBloc->adressesRestantes()} adresse(s)."];
            }
        }

        // ---------- Activité récente (derniers enregistrements, toutes entités) ----------
        $activite = collect()
            ->concat(PlageIp::latest()->take(4)->get()->map(fn ($p) =>
                ['quand' => $p->created_at, 'texte' => "Bloc {$p->reseau} ajouté", 'type' => "Sous-bloc d'adresses"]))
            ->concat(Client::latest()->take(4)->get()->map(fn ($c) =>
                ['quand' => $c->created_at, 'texte' => "Client « {$c->nom} » enregistré", 'type' => 'Client']))
            ->concat(SessionBgp::with('operateur')->latest()->take(4)->get()->map(fn ($s) =>
                ['quand' => $s->created_at, 'texte' => "Session BGP vers {$s->operateur->nom} créée", 'type' => 'BGP']))
            ->concat(AllocationRessource::with('client')->latest()->take(4)->get()->map(fn ($a) =>
                ['quand' => $a->created_at, 'texte' => "{$a->bande_passante_souscrite} Mbps alloués à {$a->client->nom}", 'type' => 'Bande passante']))
            ->sortByDesc('quand')->take(8)->values();        // Fusion triée, 8 éléments max

        // ---------- Envoi de toutes les données à la vue ----------
        return view('dashboard', [
            'nbOperateurs'       => Operateur::count(),
            'nbSessionsActives'  => SessionBgp::where('etat', 'active')->count(),
            'nbSessionsTotal'    => SessionBgp::count(),
            'nbTechnologies'     => Technologie::count(),
            'nbEquipements'      => Equipement::count(),
            'nbPlagesLibres'     => $nbPlagesLibres,
            'nbPlagesUtilisees'  => $nbPlagesUtilisees,
            'nbPlagesTotal'      => $nbPlagesTotal,
            // Taux d'occupation des plages IP (%)
            'tauxPlages'         => $nbPlagesTotal > 0 ? (int) round($nbPlagesUtilisees / $nbPlagesTotal * 100) : 0,
            'nbClients'          => Client::count(),
            'ipv4'               => $ipv4,
            'ipv6'               => $ipv6,
            'capaciteTotale'     => $capaciteTotale,
            'bandeSouscrite'     => $bandeSouscrite,
            'capaciteRestante'   => $capaciteRestante,
            'tauxBandePassante'  => $tauxBandePassante,
            'bpParTechnologie'   => $bpParTechnologie,
            'alertes'            => $alertes,
            'activite'           => $activite,
            'sousBlocs'          => $sousBlocs,             // [ÉPUISEMENT] Remplissage de chaque sous-bloc
            // [PARAMÈTRES AS] Identité du réseau local, en lecture seule (renvoi vers la page de configuration)
            'configurationAs'    => ConfigurationAs::courante(),
        ]);
    }

    /**
     * [ÉPUISEMENT] Tous les sous-blocs, avec leur nombre d'adresses attribuées, triés du
     * plus rempli au moins rempli.
     *
     * [PERFORMANCE] Le décompte est fait en UNE SEULE requête groupée (PostgreSQL teste
     * l'appartenance des IP d'équipements avec l'opérateur <<= sur le type inet), puis
     * injecté dans chaque modèle : aucune requête supplémentaire à l'affichage.
     */
    private function sousBlocsAvecRemplissage()
    {
        $sousBlocs = PlageIp::with('client')->orderBy('reseau')->get();

        if ($sousBlocs->isEmpty()) {
            return $sousBlocs;
        }

        $comptes = DB::table('plages_ip AS p')
            ->leftJoin('equipements AS e', function ($jointure) {
                $jointure->whereNotNull('e.adresse_ip')
                         ->whereRaw('e.adresse_ip::inet <<= p.reseau');
            })
            ->groupBy('p.id')
            ->selectRaw('p.id, COUNT(e.id) AS total')
            ->pluck('total', 'p.id');

        foreach ($sousBlocs as $sousBloc) {
            $sousBloc->definirAdressesAttribuees((int) ($comptes[$sousBloc->id] ?? 0));
        }

        // Les plus proches de la saturation d'abord : c'est ce qui demande une action.
        return $sousBlocs->sortByDesc(fn ($b) => $b->tauxRemplissage())->values();
    }

    /**
     * [IPv4/IPv6] Occupation d'une version du protocole, rapportée à la PLAGE PRINCIPALE
     * reçue du RIR (saisie dans « Ressources Internet »).
     *
     * Exemple concret en IPv4 avec une plage principale 102.64.0.0/19 :
     *   capacité  = 2^(32-19)          = 8 192 adresses
     *   utilisées = somme des sous-blocs =   608 adresses
     *   restantes = 8 192 - 608         = 7 584 adresses
     *
     * En IPv6, tout est compté en sous-réseaux /64 (les adresses se compteraient en
     * milliards de milliards, ce qui n'a aucun sens opérationnel).
     */
    private function statistiquesVersion(int $version): array
    {
        $requete = PlageIp::where('version_ip', $version);  // Sous-blocs de cette version uniquement

        // Somme des tailles des sous-blocs déclarés = ce qui est DÉJÀ découpé dans la plage principale.
        // Calcul délégué à PostgreSQL via masklen() : une seule requête, quel que soit le nombre de blocs.
        $utilisees = null;
        if (DB::getDriverName() === 'pgsql') {
            $utilisees = $version === 4
                ? (float) PlageIp::where('version_ip', 4)
                    ->selectRaw('COALESCE(SUM(POWER(2, 32 - masklen(reseau))), 0) AS c')->value('c')
                : (float) PlageIp::where('version_ip', 6)->whereRaw('masklen(reseau) <= 64')
                    ->selectRaw('COALESCE(SUM(POWER(2, 64 - masklen(reseau))), 0) AS c')->value('c');
        }

        // Capacité totale de la plage principale (null tant qu'elle n'est pas renseignée).
        $configuration = ConfigurationAs::courante();
        $capacite      = $configuration->capacite($version);
        $prefixe       = $version === 4 ? $configuration->prefixe_ipv4 : $configuration->prefixe_ipv6;

        // Restant et taux d'occupation, uniquement si la plage principale est connue.
        $restantes = ($capacite !== null && $utilisees !== null) ? max(0, $capacite - $utilisees) : null;
        $taux      = ($capacite > 0 && $utilisees !== null) ? (int) round($utilisees / $capacite * 100) : null;

        return [
            'prefixe'    => $prefixe,                                            // Plage principale (CIDR)
            'capacite'   => $capacite,                                           // Capacité globale
            'utilisees'  => $utilisees,                                          // Déjà découpé en sous-blocs
            'restantes'  => $restantes,                                          // Encore disponible
            'taux'       => $taux,                                               // Taux d'occupation (%)
            'unite'      => $version === 4 ? 'adresses' : 'réseaux /64',         // Unité d'affichage
            'sousBlocs'  => (clone $requete)->count(),                           // Nombre de sous-blocs
            'affectes'   => (clone $requete)->where('statut', 'utilisee')->count(), // Dont affectés à un client
            // Bande passante souscrite par les clients possédant des sous-blocs de cette version
            'bande'      => (int) AllocationRessource::whereIn('client_id',
                                (clone $requete)->whereNotNull('client_id')->pluck('client_id'))
                                ->sum('bande_passante_souscrite'),
        ];
    }
}
