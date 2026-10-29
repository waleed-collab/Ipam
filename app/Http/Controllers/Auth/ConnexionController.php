<?php
// Contrôleur : ConnexionController
// Gère la connexion et la déconnexion des utilisateurs (formulaire de login classique).

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;                        // Contrôleur de base Laravel
use App\Models\JournalAudit;                                // [AUDIT] Journal des connexions/déconnexions
use Illuminate\Http\Request;                                // Requête HTTP entrante
use Illuminate\Support\Facades\Auth;                        // Façade d'authentification Laravel

class ConnexionController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     * [FIX SÉCURITÉ] En-têtes anti-cache : sans eux, le navigateur peut resservir le formulaire
     * depuis son cache (bouton "précédent") alors qu'une session est déjà active, ce qui créait
     * des comportements incohérents (identifiants saisis mais ignorés).
     */
    public function afficherFormulaire()
    {
        return response()
            ->view('auth.login')                            // Rend la vue resources/views/auth/login.blade.php
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')  // Jamais mis en cache
            ->header('Pragma', 'no-cache');                 // Compatibilité anciens navigateurs/proxies
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function connecter(Request $request)
    {
        // Validation des champs : email valide et mot de passe obligatoires.
        $identifiants = $request->validate([
            'email'    => ['required', 'email'],            // Champ email requis et bien formé
            'password' => ['required'],                     // Mot de passe requis
        ]);

        // [FIX SÉCURITÉ] Si une session est DÉJÀ active (formulaire resservi par le cache du
        // navigateur), on la ferme proprement AVANT de traiter les nouveaux identifiants.
        // Sans cela, les identifiants saisis étaient ignorés et l'utilisateur restait connecté
        // sous l'ancien compte — source du bug "je saisis admin mais j'arrive sur la page client".
        if (Auth::check()) {
            JournalAudit::consigner('deconnexion', description: 'Déconnexion automatique (changement de compte).');
            Auth::logout();                                 // Ferme la session du compte précédent
        }

        // Tentative d'authentification ; "remember" active le cookie "se souvenir de moi".
        if (Auth::attempt($identifiants, $request->boolean('remember'))) {
            $request->session()->regenerate();              // Nouvelle session (protection contre la fixation)

            // [AUDIT] Trace la connexion réussie : qui, quand, depuis quelle adresse IP.
            JournalAudit::consigner('connexion', description: 'Connexion réussie.');

            // [FIX SÉCURITÉ] Redirection STRICTE selon le rôle (plus de redirect()->intended()).
            // L'ancienne version renvoyait vers la dernière page mémorisée en session : après une
            // expiration de session admin, un client fraîchement connecté était renvoyé vers une
            // page admin => erreur 403. Chaque rôle atterrit désormais TOUJOURS sur sa page d'accueil.
            return Auth::user()->estClient()
                ? redirect()->route('portail.accueil')      // Espace client (lecture seule)
                : redirect()->route('tableau-de-bord');     // Interface d'administration
        }

        // Échec : on revient au formulaire avec un message d'erreur, en conservant l'email saisi.
        return back()
            ->withErrors(['email' => 'Identifiants incorrects.'])   // Message affiché sous le champ
            ->onlyInput('email');                                   // Ne pré-remplit que l'email
    }

    /**
     * Déconnecte l'utilisateur et invalide sa session.
     */
    public function deconnecter(Request $request)
    {
        // [AUDIT] Trace la déconnexion AVANT l'invalidation (l'utilisateur doit encore être résolvable).
        JournalAudit::consigner('deconnexion', description: 'Déconnexion.');

        Auth::logout();                                     // Supprime l'authentification
        $request->session()->invalidate();                  // Détruit toutes les données de session
        $request->session()->regenerateToken();             // Régénère le jeton CSRF
        return redirect()->route('login');                  // Retour à la page de connexion
    }
}
