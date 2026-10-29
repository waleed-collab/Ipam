<?php
// Modèle Eloquent : InterfaceReseau
// Interface d'un équipement BACKBONE local (cadre "Local" du schéma d'architecture).
// NB : la classe ne peut pas s'appeler "Interface" (mot réservé du langage PHP).
//  - type "internationale" : relations externes — nom, @IP, bande passante, neighbor (opérateur),
//    l'AS local étant lu dans la configuration de l'AS ;
//  - type "acces"          : nom, @IP, bande passante, équipement d'accès derrière, technologie d'accès.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterfaceReseau extends Model
{
    /** Nom de table explicite (le modèle ne suit pas la pluralisation automatique). */
    protected $table = 'interfaces';

    /** Colonnes remplissables en masse. */
    protected $fillable = [
        'equipement_id', 'nom', 'adresse_ip', 'bande_passante', 'type',
        'operateur_id', 'equipement_distant_id', 'technologie_id',
    ];

    /** Relation : l'équipement backbone qui porte cette interface. */
    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class, 'equipement_id');
    }

    /** [CÔTÉ INTERNATIONAL] Relation : le neighbor (opérateur externe) joint par l'interface. */
    public function operateur(): BelongsTo
    {
        return $this->belongsTo(Operateur::class, 'operateur_id');
    }

    /** [CÔTÉ ACCÈS] Relation : l'équipement d'accès raccordé derrière l'interface. */
    public function equipementDistant(): BelongsTo
    {
        return $this->belongsTo(Equipement::class, 'equipement_distant_id');
    }

    /** [CÔTÉ ACCÈS] Relation : la technologie d'accès desservie derrière l'interface. */
    public function technologie(): BelongsTo
    {
        return $this->belongsTo(Technologie::class, 'technologie_id');
    }

    /** Relation : les sessions BGP établies au travers de cette interface (côté international). */
    public function sessionsBgp(): HasMany
    {
        return $this->hasMany(SessionBgp::class, 'interface_id');
    }

    /** Vrai si l'interface est tournée vers les opérateurs internationaux. */
    public function estInternationale(): bool
    {
        return $this->type === 'internationale';
    }
}
