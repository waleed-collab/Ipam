<?php
// Modèle Eloquent : User
// Représente un compte utilisateur de l'application avec son rôle (admin, manager, client).

namespace App\Models;                                       // Espace de noms standard des modèles Laravel

use App\Notifications\ReinitialisationMotDePasse;           // Email français de mot de passe oublié
use Illuminate\Database\Eloquent\Relations\BelongsTo;       // Type de relation "appartient à"
use Illuminate\Foundation\Auth\User as Authenticatable;     // Classe de base gérant l'authentification
use Illuminate\Notifications\Notifiable;                    // Permet d'envoyer des notifications à l'utilisateur

class User extends Authenticatable
{
    use Notifiable;                                         // Active la fonctionnalité de notifications

    /**
     * Colonnes remplissables en masse (protection contre l'assignation de masse).
     */
    protected $fillable = ['name', 'email', 'password', 'role', 'client_id'];

    /**
     * Colonnes masquées lors de la sérialisation (jamais exposées en JSON).
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Conversions automatiques de types.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',                         // Laravel hache automatiquement le mot de passe
        ];
    }

    /**
     * Relation : la fiche client rattachée à ce compte (uniquement pour le rôle "client").
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);             // users.client_id -> clients.id
    }

    // ----- Méthodes utilitaires de vérification de rôle -----

    /** Vrai si l'utilisateur est administrateur. */
    public function estAdmin(): bool
    {
        return $this->role === 'admin';                     // Comparaison directe du rôle
    }

    /** Vrai si l'utilisateur est manager. */
    public function estManager(): bool
    {
        return $this->role === 'manager';
    }

    /** Vrai si l'utilisateur fait partie du personnel (accès complet : admin OU manager). */
    public function estPersonnel(): bool
    {
        return in_array($this->role, ['admin', 'manager']); // Les deux rôles ont les mêmes droits techniques
    }

    /** Vrai si l'utilisateur est un client (lecture seule sur son périmètre). */
    public function estClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * [AUTH] Surcharge Laravel : envoie l'email de réinitialisation en FRANÇAIS
     * (utilisé automatiquement par le workflow "mot de passe oublié").
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ReinitialisationMotDePasse($token)); // Notification personnalisée
    }
}
