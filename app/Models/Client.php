<?php
// Modèle Eloquent : Client
// Entité externe à qui l'on affecte des plages IP, des technologies, des équipements
// et [CAPACITY PLANNING] des allocations de bande passante.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;                     // Classe de base des modèles Eloquent
use Illuminate\Database\Eloquent\Relations\HasMany;         // Relation "possède plusieurs"

class Client extends Model
{
    /** Colonnes remplissables en masse. */
    protected $fillable = ['nom', 'email', 'telephone', 'adresse'];

    /**
     * Relation : les plages IP affectées à ce client.
     * (cas "Visualiser ses propres plages IP" côté portail client)
     */
    public function plagesIp(): HasMany
    {
        return $this->hasMany(PlageIp::class);              // plages_ip.client_id -> clients.id
    }

    /**
     * Relation : les équipements installés chez ce client.
     * (cas "Visualiser les équipements rattachés à un client")
     */
    public function equipements(): HasMany
    {
        return $this->hasMany(Equipement::class);           // equipements.client_id -> clients.id
    }

    /**
     * Relation : les comptes utilisateurs rattachés à cette fiche client.
     */
    public function utilisateurs(): HasMany
    {
        return $this->hasMany(User::class);                 // users.client_id -> clients.id
    }

    /** [CAPACITY PLANNING] Relation : les allocations de bande passante du client. */
    public function allocations(): HasMany
    {
        return $this->hasMany(AllocationRessource::class, 'client_id');
    }

    /** [CAPACITY PLANNING] Total de bande passante souscrite par le client (Mbps). */
    public function bandePassanteTotale(): int
    {
        return (int) $this->allocations()->sum('bande_passante_souscrite'); // Somme SQL directe
    }

    /**
     * Technologies associées au client, déduites de ses plages IP, de ses équipements
     * et désormais aussi de ses allocations de bande passante.
     * (cas "Visualiser les technologies associées" côté portail client)
     */
    public function technologiesAssociees()
    {
        // On récupère les identifiants de technologies via les plages IP du client...
        $viaPlages = $this->plagesIp()->pluck('technologie_id');
        // ...via ses équipements...
        $viaEquipements = $this->equipements()->pluck('technologie_id');
        // ...et via ses allocations de bande passante, puis on fusionne sans doublon ni valeur nulle.
        $viaAllocations = $this->allocations()->pluck('technologie_id');
        $ids = $viaPlages->merge($viaEquipements)->merge($viaAllocations)->filter()->unique();
        // On retourne enfin les technologies correspondantes, triées par nom.
        return Technologie::whereIn('id', $ids)->orderBy('nom')->get();
    }
}
