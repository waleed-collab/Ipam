<?php
// Modèle Eloquent : AllocationRessource
// [CAPACITY PLANNING] Allocation de bande passante : relie un client à une technologie
// (et éventuellement à la session BGP source) avec les débits souscrit et maximum.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllocationRessource extends Model
{
    /** Nom de table explicite (par défaut Laravel chercherait "allocation_ressources" — identique ici, mais explicite). */
    protected $table = 'allocation_ressources';

    /** Colonnes remplissables en masse. */
    protected $fillable = [
        'client_id',                 // Client bénéficiaire de la bande passante
        'technologie_id',            // Technologie d'accès utilisée (impose sa limite)
        'bande_passante_souscrite',  // Débit souscrit par le client (Mbps)
        'description',               // Libellé commercial de l'offre
    ];

    /** Relation : le client bénéficiaire. */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** Relation : la technologie d'accès (porte la limite debit_max_supporte). */
    public function technologie(): BelongsTo
    {
        return $this->belongsTo(Technologie::class);
    }
}
