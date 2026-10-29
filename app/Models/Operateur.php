<?php
// Modèle Eloquent : Operateur
// Fournisseur / pair réseau externe avec lequel des sessions BGP sont établies.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operateur extends Model
{
    /** Nom de table explicite (le pluriel français "operateurs" est déjà correct, mais on le fixe). */
    protected $table = 'operateurs';

    /** Colonnes remplissables en masse. */
    protected $fillable = ['nom', 'asn', 'contact', 'email_contact', 'description'];

    /**
     * Relation : les sessions BGP établies avec cet opérateur.
     * (cas "Associer un opérateur à une session BGP")
     */
    public function sessionsBgp(): HasMany
    {
        return $this->hasMany(SessionBgp::class, 'operateur_id'); // sessions_bgp.operateur_id -> operateurs.id
    }
}
