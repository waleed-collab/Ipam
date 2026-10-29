<?php
// Fichier d'amorçage de l'application (Laravel 11+).
// C'est ici que l'on enregistre l'alias "role" utilisé dans routes/web.php.

use App\Http\Middleware\VerifierRole;                       // Notre middleware de contrôle des rôles
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;      // Middleware "guest" (protège /login, /inscription...)
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Pagination\Paginator;                        // Vue de pagination personnalisée

// [FIX] Par défaut, si un utilisateur DÉJÀ connecté rouvre /login, ce middleware le redirige vers
// une route nommée "dashboard" ou "home" — qui n'existent pas dans cette appli (ici : "tableau-de-bord"
// et "portail.accueil"). Sans ce nom trouvé, Laravel se rabat sur "/", qui redirige elle-même vers
// /login : boucle de redirection infinie (ERR_TOO_MANY_REDIRECTS) pour tout utilisateur déjà connecté.
// On indique donc explicitement où renvoyer un utilisateur déjà connecté, selon son rôle.
RedirectIfAuthenticated::redirectUsing(function ($request) {
    $utilisateur = $request->user();
    return $utilisateur && $utilisateur->estClient()
        ? route('portail.accueil')
        : route('tableau-de-bord');
});

return Application::configure(basePath: dirname(__DIR__))   // Construit l'application depuis la racine
    ->withRouting(
        web: __DIR__.'/../routes/web.php',                  // Charge nos routes web
        commands: __DIR__.'/../routes/console.php',         // Commandes artisan personnalisées
        health: '/up',                                      // Route de supervision (healthcheck)
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Déclaration de l'alias : 'role' => classe VerifierRole.
        // Permet d'écrire ->middleware('role:admin,manager') dans les routes.
        $middleware->alias([
            'role' => VerifierRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Personnalisation éventuelle de la gestion des erreurs (laissée par défaut).
    })
    ->booted(function () {
        // [FIX PAGINATION] La vue de pagination livrée par Laravel est prévue pour Tailwind CSS,
        // absent de ce projet : ses chevrons SVG s'affichaient alors en pleine page. On impose
        // la vue maison (resources/views/vendor/pagination/ipam.blade.php) pour TOUTES les listes.
        Paginator::defaultView('vendor.pagination.ipam');
        Paginator::defaultSimpleView('vendor.pagination.ipam');
    })
    ->create();                                           // Retourne l'instance de l'application
