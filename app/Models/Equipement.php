<?php
// Modèle Eloquent : Equipement
// Matériel réseau (routeur, switch, antenne, modem...) lié à une technologie et parfois à un client.
// Le rôle "backbone" désigne l'équipement local central (cadre "Local" du schéma) : il porte les
// interfaces côté international (sessions BGP) et côté accès (réseau d'accès + technologies).

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipement extends Model
{
    /** Colonnes remplissables en masse. */
    protected $fillable = ['nom', 'type', 'role', 'adresse_ip', 'technologie_id', 'client_id'];

    /** Relation : la technologie d'accès de cet équipement. */
    public function technologie(): BelongsTo
    {
        return $this->belongsTo(Technologie::class);
    }

    /** Relation : le client chez qui l'équipement est installé (peut être null). */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** Relation : toutes les interfaces portées par cet équipement (backbone uniquement en pratique). */
    public function interfaces(): HasMany
    {
        return $this->hasMany(InterfaceReseau::class, 'equipement_id');
    }

    /** Relation filtrée : interfaces CÔTÉ INTERNATIONAL (relations externes / sessions BGP). */
    public function interfacesInternationales(): HasMany
    {
        return $this->interfaces()->where('type', 'internationale'); // Filtre SQL sur la colonne "type"
    }

    /** Relation filtrée : interfaces CÔTÉ ACCÈS (équipements et technologies d'accès derrière). */
    public function interfacesAcces(): HasMany
    {
        return $this->interfaces()->where('type', 'acces');
    }

    /** Vrai si l'équipement est le backbone local (porteur des interfaces). */
    public function estBackbone(): bool
    {
        return $this->role === 'backbone';
    }
}
