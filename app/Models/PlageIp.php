<?php
// Modèle Eloquent : PlageIp
// Bloc d'adresses IP (CIDR) géré par l'IPAM ; associable à une technologie et affectable à un client.
// [IPv4/IPv6] Gère nativement les deux protocoles : la version est déduite automatiquement.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlageIp extends Model
{
    /** Nom de table explicite (par défaut Laravel chercherait "plage_ips"). */
    protected $table = 'plages_ip';

    /** [PERFORMANCE] Décompte des adresses attribuées, mémorisé pour éviter les requêtes répétées. */
    private ?int $attribueesMemorisees = null;

    /** Colonnes remplissables en masse. */
    protected $fillable = ['reseau', 'version_ip', 'description', 'statut', 'technologie_id', 'client_id', 'parent_id'];

    /** Relation : la technologie d'accès associée à la plage. */
    public function technologie(): BelongsTo
    {
        return $this->belongsTo(Technologie::class);
    }

    /** Relation : le client à qui la plage est affectée (null si disponible). */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** Relation : la plage mère éventuelle (colonne conservée en base, non exploitée dans l'UI). */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(PlageIp::class, 'parent_id');
    }

    /**
     * Hook de cycle de vie exécuté avant CHAQUE sauvegarde :
     *  1. synchronise le statut avec l'affectation (client => "utilisee", sinon "disponible") ;
     *  2. déduit automatiquement la version IP (4 ou 6) du bloc CIDR saisi.
     */
    protected static function booted(): void
    {
        static::saving(function (PlageIp $plage) {
            $plage->statut = $plage->client_id ? 'utilisee' : 'disponible'; // Statut déduit du client
            // La version IP est recalculée depuis le CIDR : présence de ":" => IPv6, sinon IPv4
            $plage->version_ip = self::analyserCidr((string) $plage->reseau) ?? ($plage->version_ip ?: 4);
        });
    }

    /**
     * [IPv4/IPv6] Analyse une chaîne CIDR et retourne la version du protocole.
     *
     * @param  string|null $valeur  Chaîne à analyser (ex : "192.168.1.0/24" ou "2001:db8::/32")
     * @return int|null             4 pour IPv4, 6 pour IPv6, null si le format est invalide
     */
    public static function analyserCidr(?string $valeur): ?int
    {
        if (! $valeur || substr_count($valeur, '/') !== 1) { // Un CIDR contient exactement un "/"
            return null;
        }
        [$adresse, $masque] = explode('/', $valeur);         // Séparation adresse / longueur de préfixe
        if (! ctype_digit($masque)) {                        // Le masque doit être un entier positif
            return null;
        }
        // Adresse IPv4 valide avec un préfixe entre 0 et 32 ?
        if (filter_var($adresse, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && (int) $masque <= 32) {
            return 4;
        }
        // Adresse IPv6 valide avec un préfixe entre 0 et 128 ?
        if (filter_var($adresse, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && (int) $masque <= 128) {
            return 6;
        }
        return null;                                         // Format non reconnu
    }

    /**
     * [Recherche globale] Analyse une saisie qui peut être une IP seule OU un CIDR.
     *
     * @return int|null  4 ou 6 si la saisie est une IP/un CIDR valide, null sinon
     */
    public static function analyserIpOuCidr(?string $valeur): ?int
    {
        if (! $valeur) {
            return null;
        }
        if (str_contains($valeur, '/')) {                    // Contient un "/" => on tente le format CIDR
            return self::analyserCidr($valeur);
        }
        if (filter_var($valeur, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return 4;                                        // IP IPv4 seule
        }
        if (filter_var($valeur, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return 6;                                        // IP IPv6 seule
        }
        return null;
    }

    // ======================================================================
    // [ADRESSES] Nombre d'adresses contenues dans le bloc, selon la convention
    // déjà utilisée par le tableau de bord :
    //   IPv4 => 2^(32 - masque)  (nombre total d'adresses du bloc) ;
    //   IPv6 => 2^(64 - masque)  (nombre de sous-réseaux /64, unité usuelle en IPv6).
    // ======================================================================

    /** Longueur du préfixe (masque) de la plage, ex : 24 pour "102.64.10.0/24". */
    public function longueurPrefixe(): ?int
    {
        if (substr_count((string) $this->reseau, '/') !== 1) {
            return null;                                     // CIDR mal formé
        }
        return (int) explode('/', (string) $this->reseau)[1];
    }

    /**
     * Nombre d'adresses du bloc (convention de l'application).
     *
     * @return int|null  IPv4 : 2^(32-masque) adresses ; IPv6 : 2^(64-masque) réseaux /64
     *                   (1 si le préfixe est déjà égal ou plus long que /64) ; null si CIDR invalide.
     */
    public function nombreAdresses(): ?int
    {
        $masque = $this->longueurPrefixe();
        if ($masque === null) {
            return null;                                     // Format non exploitable
        }
        if ((int) $this->version_ip === 6) {
            return $masque >= 64 ? 1 : 2 ** (64 - $masque);  // Compté en sous-réseaux /64
        }
        return 2 ** (32 - $masque);                          // IPv4 : adresses totales du bloc
    }

    /**
     * Unité d'affichage associée au décompte ci-dessus (pour l'interface).
     *
     * @return string  "adresses" en IPv4, "réseaux /64" en IPv6.
     */
    public function uniteAdresses(): string
    {
        return (int) $this->version_ip === 6 ? 'réseaux /64' : 'adresses';
    }

    // ======================================================================
    // [ÉPUISEMENT] Consommation réelle À L'INTÉRIEUR du sous-bloc.
    // Une adresse est considérée comme consommée dès qu'un équipement la porte.
    // On peut ainsi savoir combien il reste avant que le sous-bloc soit plein.
    // ======================================================================

    /**
     * Nombre d'adresses réellement attribuées dans ce sous-bloc.
     * Le test d'appartenance est fait par PostgreSQL via l'opérateur <<= sur le type inet.
     *
     * @return int  Nombre d'équipements dont l'IP tombe à l'intérieur du sous-bloc
     */
    public function adressesAttribuees(): int
    {
        // [PERFORMANCE] Résultat mémorisé : la fiche et la liste appellent plusieurs fois
        // ce décompte (restant, taux…). Sans cela, chaque appel relancerait une requête.
        if ($this->attribueesMemorisees !== null) {
            return $this->attribueesMemorisees;
        }

        if (! $this->reseau) {
            return $this->attribueesMemorisees = 0;
        }

        return $this->attribueesMemorisees = (int) Equipement::whereNotNull('adresse_ip')
            ->whereRaw('adresse_ip::inet <<= ?::inet', [$this->reseau])
            ->count();
    }

    /**
     * [PERFORMANCE] Renseigne le décompte sans requête, depuis un calcul groupé.
     * Utilisé par la liste des sous-blocs : une seule requête pour toute la page.
     */
    public function definirAdressesAttribuees(int $nombre): void
    {
        $this->attribueesMemorisees = $nombre;
    }

    /**
     * Nombre d'adresses réellement AFFECTABLES du sous-bloc.
     * En IPv4, l'adresse de réseau et celle de diffusion (broadcast) ne sont pas attribuables :
     * un /24 offre donc 254 adresses utiles et non 256. Cette règle ne s'applique qu'aux
     * blocs plus larges qu'un /31, et pas du tout en IPv6 (pas de broadcast).
     *
     * @return int|null  Capacité utile, ou null si le CIDR est invalide
     */
    public function adressesAffectables(): ?int
    {
        $total = $this->nombreAdresses();

        if ($total === null) {
            return null;
        }

        return ((int) $this->version_ip === 4 && $total > 2) ? $total - 2 : $total;
    }

    /**
     * Nombre d'adresses encore libres avant épuisement complet du sous-bloc.
     *
     * @return int|null  Restant, ou null si le CIDR est invalide
     */
    public function adressesRestantes(): ?int
    {
        $affectables = $this->adressesAffectables();

        return $affectables === null ? null : max(0, $affectables - $this->adressesAttribuees());
    }

    /**
     * Taux de remplissage du sous-bloc, en pourcentage entier (0 si capacité inconnue).
     */
    public function tauxRemplissage(): int
    {
        $affectables = $this->adressesAffectables();

        if (! $affectables) {
            return 0;
        }

        return (int) round($this->adressesAttribuees() / $affectables * 100);
    }
}
