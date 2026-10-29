<?php
// Contrôleur : MotDePasseOublieController
// [AUTH] Workflow COMPLET "mot de passe oublié" de Laravel :
//   1. demande du lien  -> 2. envoi de l'email  -> 3. formulaire de réinitialisation  -> 4. nouveau mot de passe.

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as ReglePassword;

class MotDePasseOublieController extends Controller
{
    /**
     * Étape 1 : affiche le formulaire "Mot de passe oublié" (saisie de l'email).
     */
    public function afficherDemande()
    {
        return view('auth.mot-de-passe-oublie');
    }

    /**
     * Étape 2 : envoie le lien de réinitialisation par email via le broker Laravel.
     */
    public function envoyerLien(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);     // L'email doit être valide

        // Le broker vérifie l'existence du compte, génère le jeton et envoie l'email
        $statut = Password::sendResetLink($request->only('email'));

        // Par sécurité, on affiche le MÊME message que l'email existe ou non
        // (empêche de deviner quels emails possèdent un compte).
        return back()->with('succes', 'Si un compte existe avec cette adresse, un lien de réinitialisation vient de lui être envoyé.');
    }

    /**
     * Étape 3 : affiche le formulaire de réinitialisation (accessible via le lien reçu par email).
     */
    public function afficherReinitialisation(Request $request, string $token)
    {
        return view('auth.reinitialiser', [
            'token' => $token,                              // Jeton contenu dans le lien
            'email' => $request->query('email'),            // Email pré-rempli depuis l'URL
        ]);
    }

    /**
     * Étape 4 : enregistre le NOUVEAU mot de passe si le jeton est valide.
     */
    public function reinitialiser(Request $request)
    {
        $request->validate([
            'token'    => ['required'],                     // Jeton obligatoire (issu de l'email)
            'email'    => ['required', 'email'],            // Email du compte
            // Nouveau mot de passe : min. 8 caractères, lettres + chiffres, confirmé
            'password' => ['required', 'confirmed', ReglePassword::min(8)->letters()->numbers()],
        ]);

        // Le broker vérifie le jeton puis exécute notre fonction de mise à jour
        $statut = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $utilisateur, string $motDePasse) {
                $utilisateur->forceFill([
                    'password'       => $motDePasse,        // Haché automatiquement (cast "hashed")
                    'remember_token' => Str::random(60),    // Invalide les anciens cookies "se souvenir"
                ])->save();
            }
        );

        // Succès => retour à la connexion ; échec (jeton expiré...) => message d'erreur
        return $statut === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('succes', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.')
            : back()->withErrors(['email' => 'Ce lien de réinitialisation est invalide ou a expiré.']);
    }
}
