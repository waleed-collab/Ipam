<?php
// Modèle Eloquent : PrefixeBgp
// Préfixe réseau échangé (reçu ou annoncé) sur une session BGP donnée.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrefixeBgp extends Model
{
    /** Nom de table explicite. */
    protected $table = 'prefixes_bgp';

    /** Colonnes remplissables en masse. */
    protected $fillable = ['session_bgp_id', 'prefixe', 'type'];

    /** Relation : la session BGP à laquelle appartient ce préfixe. */
    public function sessionBgp(): BelongsTo
    {
        return $this->belongsTo(SessionBgp::class, 'session_bgp_id');
    }
}
