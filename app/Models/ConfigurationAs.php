<?php
// Modèle Eloquent : ConfigurationAs
// Paramètres du Système Autonome local (une seule ligne utilisée : configuration "singleton").

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigurationAs extends Model
{
    /** Nom de table explicite (pas de pluralisation automatique). */
    protected $table = 'configuration_as';

    /** Colonnes remplissables en masse. */
    protected $fillable = ['asn_local', 'nom_reseau', 'router_id', 'description', 'prefixe_ipv4', 'prefixe_ipv6'];

    /**
     * [CAPACITÉ] Nombre total d'unités contenues dans une plage principale.
     * Convention de l'application, identique à celle des sous-blocs :
     *   IPv4 => 2^(32 - masque)  adresses      (ex : /19 => 2^13 = 8 192)
     *   IPv6 => 2^(64 - masque)  réseaux /64   (ex : /32 => 2^32)
     *
     * @param  int      $version  4 ou 6
     * @return int|null           Capacité totale, ou null si aucune plage n'est renseignée
     */
    public function capacite(int $version): ?int
    {
        $prefixe = $version === 4 ? $this->prefixe_ipv4 : $this->prefixe_ipv6;

        if (! $prefixe || substr_count((string) $prefixe, '/') !== 1) {
            return null;                                    // Non renseignée ou format invalide
        }

        $masque = (int) explode('/', (string) $prefixe)[1];
        $base   = $version === 4 ? 32 : 64;

        return $masque >= $base ? 1 : 2 ** ($base - $masque);
    }

    /**
     * Récupère (ou crée avec des valeurs par défaut) l'unique ligne de configuration.
     * Garantit qu'il existe toujours une configuration à afficher/modifier.
     */
    public static function courante(): self
    {
        // firstOrCreate : retourne la première ligne, ou l'insère si la table est vide.
        return static::firstOrCreate([], [
            'asn_local'  => 0,                              // ASN par défaut (à modifier par l'admin)
            'nom_reseau' => 'Réseau local',                 // Nom par défaut
        ]);
    }
}
