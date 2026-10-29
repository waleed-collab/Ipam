<?php
// Contrôleur : RechercheGlobaleController
// [RECHERCHE GLOBALE] Barre de recherche unique (Admin/Manager) acceptant :
//   - une adresse IPv4 ou IPv6, ou un bloc CIDR  -> opérateur PostgreSQL ">>=" (contient ou égale)
//   - un ASN (nombre)                            -> sessions BGP + opérateurs
//   - du texte libre                             -> clients, opérateurs, technologies
// Le résultat est une FICHE COMPLÈTE avec toutes les entités liées (jointures Eloquent with()).

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Operateur;
use App\Models\PlageIp;
use App\Models\SessionBgp;
use App\Models\Technologie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RechercheGlobaleController extends Controller
{
    /**
     * Point d'entrée unique : détecte le type de saisie puis délègue à la bonne stratégie.
     */
    public function rechercher(Request $request)
    {
        $q = trim((string) $request->query('q', ''));       // Terme saisi dans la barre de recherche

        // Résultat par défaut (structure toujours identique pour simplifier la vue)
        $resultat = [
            'q' => $q, 'type' => null,
            'plages' => collect(), 'sessions' => collect(),
            'operateurs' => collect(), 'clients' => collect(), 'technologies' => collect(),
        ];

        if ($q === '') {                                    // Saisie vide : page de recherche vierge
            return view('recherche.resultats', $resultat);
        }

        // ---------- Détection du type de saisie ----------
        $versionIp = PlageIp::analyserIpOuCidr($q);         // 4/6 si IP ou CIDR valide, null sinon

        if ($versionIp !== null) {
            return $this->rechercherParIp($q, $versionIp, $resultat);   // Stratégie IP / CIDR
        }
        if (ctype_digit($q)) {
            return $this->rechercherParAsn((int) $q, $resultat);        // Stratégie ASN
        }
        return $this->rechercherParTexte($q, $resultat);                // Stratégie texte libre
    }

    /**
     * Stratégie 1 : recherche par IP ou CIDR (IPv4 comme IPv6).
     * Utilise l'opérateur d'inclusion réseau PostgreSQL ">>=" pour retrouver la ou les
     * plages PARENTES contenant la saisie, avec client, technologie et bande passante.
     */
    private function rechercherParIp(string $q, int $version, array $resultat)
    {
        $resultat['type'] = "ip{$version}";                 // Pour l'affichage ("ip4" ou "ip6")

        // Requête de base avec TOUTES les jointures nécessaires à la fiche complète
        $requete = PlageIp::with([
            'technologie',                                  // Technologie de la plage
            'client.allocations.technologie',               // Client + son profil de bande passante
            'client.equipements',                           // Équipements du client
        ]);

        if (DB::getDriverName() === 'pgsql') {
            // ">>=" : la plage contient (ou est égale à) l'adresse/le bloc saisi.
            // Tri par masklen() décroissant : la plage la PLUS SPÉCIFIQUE (parente directe) d'abord.
            $plages = $requete->whereRaw('reseau >>= ?::inet', [$q])
                              ->orderByRaw('masklen(reseau) DESC')
                              ->get();
        } else {
            // Repli hors PostgreSQL (ex : tests SQLite) : correspondance exacte uniquement
            $plages = $requete->where('reseau', $q)->get();
        }

        $resultat['plages'] = $plages;

        // Bonus : la saisie correspond-elle aussi à un préfixe BGP ou à l'IP d'un voisin ?
        $resultat['sessions'] = SessionBgp::with(['operateur', 'prefixes'])
            ->where('ip_voisin', explode('/', $q)[0])       // IP du voisin (sans le masque éventuel)
            ->orWhereHas('prefixes', fn ($p) => $p->where('prefixe', $q)) // Préfixe annoncé/reçu identique
            ->get();

        return view('recherche.resultats', $resultat);
    }

    /**
     * Stratégie 2 : recherche par ASN.
     * Cherche dans SESSION_BGP (ASN distant) et chez les opérateurs, puis remonte
     * les plages IP et clients associés via les allocations de bande passante.
     */
    private function rechercherParAsn(int $asn, array $resultat)
    {
        $resultat['type'] = 'asn';

        // Sessions BGP dont l'ASN distant correspond, avec opérateur et préfixes
        $resultat['sessions'] = SessionBgp::with([
            'operateur',                                    // Opérateur de la session
            'prefixes',                                     // Préfixes reçus + annoncés
        ])->where('asn_distant', $asn)->get();

        // Opérateurs dont l'ASN propre correspond (avec leurs sessions)
        $resultat['operateurs'] = Operateur::with('sessionsBgp')
            ->where('asn', $asn)->get();

        return view('recherche.resultats', $resultat);
    }

    /**
     * Stratégie 3 : texte libre — clients, opérateurs et technologies par nom/email.
     */
    private function rechercherParTexte(string $q, array $resultat)
    {
        $resultat['type'] = 'texte';
        $motif = '%' . $q . '%';                            // Motif SQL LIKE

        // Clients par nom ou email, avec leur périmètre complet pour la fiche
        $resultat['clients'] = Client::with(['plagesIp.technologie', 'allocations.technologie', 'equipements'])
            ->whereRaw('LOWER(nom) LIKE LOWER(?)', [$motif])
            ->orWhereRaw('LOWER(email) LIKE LOWER(?)', [$motif])
            ->get();

        // Opérateurs par nom
        $resultat['operateurs'] = Operateur::with('sessionsBgp')
            ->whereRaw('LOWER(nom) LIKE LOWER(?)', [$motif])->get();

        // Technologies par nom
        $resultat['technologies'] = Technologie::withCount(['plagesIp', 'equipements'])
            ->whereRaw('LOWER(nom) LIKE LOWER(?)', [$motif])->get();

        return view('recherche.resultats', $resultat);
    }
}
