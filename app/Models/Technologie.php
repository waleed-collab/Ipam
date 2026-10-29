<?php
// Modèle Eloquent : Technologie
// Technologie d'accès réseau : Fibre optique, Faisceau hertzien, VSAT, 4G/LTE, etc.
// [CAPACITY PLANNING] Porte la limite technique de débit par client (Mbps).

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Technologie extends Model
{
    /** Colonnes remplissables en masse. */
    protected $fillable = ['nom', 'description', 'debit_max_supporte'];

    /**
     * Relation : les équipements utilisant cette technologie.
     * (cas "Consulter les équipements par technologie")
     */
    public function equipements(): HasMany
    {
        return $this->hasMany(Equipement::class);           // equipements.technologie_id -> technologies.id
    }

    /** Relation : les plages IP associées à cette technologie. */
    public function plagesIp(): HasMany
    {
        return $this->hasMany(PlageIp::class);              // plages_ip.technologie_id -> technologies.id
    }

    /** [CAPACITY PLANNING] Relation : les allocations de bande passante sur cette technologie. */
    public function allocations(): HasMany
    {
        return $this->hasMany(AllocationRessource::class, 'technologie_id');
    }
}
