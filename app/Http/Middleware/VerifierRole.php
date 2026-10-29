<?php
// Middleware : VerifierRole
// Point central de la SÉCURITÉ applicative : bloque l'accès aux routes selon le rôle de l'utilisateur.
// Utilisation dans routes/web.php : ->middleware('role:admin,manager') ou ->middleware('role:client')

namespace App\Http\Middleware;

use Closure;                                                // Représente la suite de la chaîne de traitement
use Illuminate\Http\Request;                                // Objet requête HTTP entrante
use Symfony\Component\HttpFoundation\Response;              // Type de retour standard d'une réponse HTTP

class VerifierRole
{
    /**
     * Filtre la requête entrante.
     *
     * @param  Request  $request   La requête HTTP en cours
     * @param  Closure  $next      Le maillon suivant (contrôleur ou middleware suivant)
     * @param  string   ...$roles  Liste des rôles autorisés, passés depuis la route (ex : "admin", "manager")
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $utilisateur = $request->user();                    // Utilisateur actuellement authentifié (ou null)

        // 1) Si personne n'est connecté, on redirige vers la page de connexion.
        if (! $utilisateur) {
            return redirect()->route('login');              // Redirection HTTP 302 vers /login
        }

        // 2) Si le rôle de l'utilisateur ne figure pas dans la liste autorisée, accès refusé.
        if (! in_array($utilisateur->role, $roles)) {
            abort(403, "Accès refusé : votre rôle ne permet pas cette action."); // Erreur HTTP 403
        }

        // 3) Sinon, la requête continue son chemin normalement vers le contrôleur.
        return $next($request);
    }
}
