<?php
// Modèle Eloquent : SessionBgp
// Session de peering/transit BGP entre l'AS local et un opérateur externe.
// [CAPACITY PLANNING] Porte la capacité physique totale (Mbps) fournie par l'opérateur.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessionBgp extends Model
{
    /** Nom de table explicite (par défaut Laravel chercherait "session_bgps"). */
    protected $table = 'sessions_bgp';

    /** Colonnes remplissables en masse. */
    protected $fillable = ['operateur_id', 'interface_id', 'ip_voisin', 'asn_distant',
                           'ip_locale', 'etat', 'capacite_totale', 'description'];

    /** Relation : l'opérateur externe de cette session (le NEIGHBOR du schéma). */
    public function operateur(): BelongsTo
    {
        return $this->belongsTo(Operateur::class, 'operateur_id');
    }

    /** Relation : l'interface internationale du backbone par laquelle passe la session. */
    public function interfaceLocale(): BelongsTo
    {
        return $this->belongsTo(InterfaceReseau::class, 'interface_id');
    }

    /** ASN LOCAL de la session : paramètre global de l'AS (table configuration_as). */
    public function asnLocal(): int
    {
        return (int) ConfigurationAs::courante()->asn_local;
    }

    /** Relation : tous les préfixes (reçus + annoncés) de la session. */
    public function prefixes(): HasMany
    {
        return $this->hasMany(PrefixeBgp::class, 'session_bgp_id');
    }

    /** Relation filtrée : uniquement les préfixes REÇUS du voisin. */
    public function prefixesRecus(): HasMany
    {
        return $this->prefixes()->where('type', 'recu');    // Filtre SQL sur la colonne "type"
    }

    /** Relation filtrée : uniquement les préfixes ANNONCÉS au voisin. */
    public function prefixesAnnonces(): HasMany
    {
        return $this->prefixes()->where('type', 'annonce');
    }
}
