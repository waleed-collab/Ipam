<?php
// Notification : ReinitialisationMotDePasse
// [AUTH] Email en FRANÇAIS envoyé lors d'une demande de "mot de passe oublié".
// Remplace la notification anglaise par défaut de Laravel.

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReinitialisationMotDePasse extends Notification
{
    /** @param string $token Jeton de réinitialisation généré par Laravel */
    public function __construct(public string $token)
    {
    }

    /** Canal d'envoi : email uniquement. */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /** Construction du message email (lien vers le formulaire de réinitialisation). */
    public function toMail(object $notifiable): MailMessage
    {
        // URL signée vers la page de réinitialisation, avec le jeton + l'email pré-rempli
        $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->email]);

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe — IPAM')      // Objet de l'email
            ->greeting('Bonjour,')                                          // Salutation
            ->line('Vous recevez cet email car une demande de réinitialisation de mot de passe a été effectuée pour votre compte.')
            ->action('Réinitialiser mon mot de passe', $url)                // Bouton d'action
            ->line('Ce lien expirera dans 60 minutes.')                     // Durée de validité
            ->line("Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email.")
            ->salutation('L\'équipe IPAM');                                 // Signature
    }
}
