<?php
// Fichier de routes : routes/web.php
// C'est ICI que la matrice des droits (tableau des cas d'utilisation) est appliquée :
//  - middleware 'role:admin,manager' => réservé au personnel (accès complet)
//  - middleware 'role:client'        => réservé aux clients (lecture seule, périmètre propre)

use App\Http\Controllers\AllocationRessourceController;
use App\Http\Controllers\Auth\ConnexionController;
use App\Http\Controllers\Auth\InscriptionController;
use App\Http\Controllers\Auth\MotDePasseOublieController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ConfigurationAsController;
use App\Http\Controllers\EquipementController;
use App\Http\Controllers\ExportClientController;
use App\Http\Controllers\JournalAuditController;
use App\Http\Controllers\OperateurController;
use App\Http\Controllers\PlageIpController;
use App\Http\Controllers\PortailClientController;
use App\Http\Controllers\RechercheGlobaleController;
use App\Http\Controllers\SessionBgpController;
use App\Http\Controllers\TableauDeBordController;
use App\Http\Controllers\TechnologieController;
use Illuminate\Support\Facades\Route;

// ---------- Page d'accueil : simple redirection vers la connexion ----------
// [ATTENTION] Ne PAS activer « php artisan route:cache » sur ce projet : sous Laravel 13.20,
// la route racine mise en cache perd la méthode GET (erreur 405 « The GET method is not
// supported for route / », toutes les autres méthodes étant acceptées). Le cache de
// CONFIGURATION, lui, fonctionne parfaitement et reste actif.
// À noter aussi : Route::redirect('/', '/login') est à proscrire ici, car l'application est
// servie depuis un sous-dossier (/ipam/public) et la cible serait résolue à la racine du
// domaine. redirect()->route('login') génère l'URL complète et correcte.
Route::get('/', fn () => redirect()->route('login'));       // La racine mène au formulaire de login

// ---------- Routes d'authentification (accessibles aux visiteurs) ----------
Route::middleware('guest')->group(function () {             // "guest" = utilisateurs NON connectés
    Route::get('/login', [ConnexionController::class, 'afficherFormulaire'])->name('login');  // Formulaire

    // [AUTH] Inscription réservée aux CLIENTS (crée la fiche client + le compte, rôle "client" imposé)
    Route::get('/inscription', [InscriptionController::class, 'afficherFormulaire'])->name('register');
    Route::post('/inscription', [InscriptionController::class, 'inscrire'])->name('register.post');

    // [AUTH] Workflow complet "Mot de passe oublié" (noms de routes = convention Laravel,
    // indispensables au broker de réinitialisation et à la notification email)
    Route::get('/mot-de-passe-oublie', [MotDePasseOublieController::class, 'afficherDemande'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [MotDePasseOublieController::class, 'envoyerLien'])->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [MotDePasseOublieController::class, 'afficherReinitialisation'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [MotDePasseOublieController::class, 'reinitialiser'])->name('password.update');
});

// [FIX SÉCURITÉ] La soumission du login est VOLONTAIREMENT hors du middleware "guest" :
// si le navigateur resservait le formulaire depuis son cache alors qu'une session était encore
// active, "guest" redirigeait sans traiter les identifiants (l'utilisateur restait connecté
// sous l'ANCIEN compte). Le contrôleur ferme désormais lui-même toute session active avant
// d'authentifier les identifiants soumis (changement de compte propre).
// [SÉCURITÉ] Limitation anti-force brute : 5 tentatives par minute et par adresse IP.
// Au-delà, Laravel renvoie une erreur 429 avec un délai d'attente. Sans cela, un attaquant
// pouvait enchaîner des milliers de mots de passe sans aucune entrave.
Route::post('/login', [ConnexionController::class, 'connecter'])
    ->middleware('throttle:5,1')->name('login.post');

// La déconnexion nécessite d'être connecté (méthode POST pour éviter les déconnexions par simple lien).
Route::post('/logout', [ConnexionController::class, 'deconnecter'])
    ->middleware('auth')->name('logout');

// ---------- Zone PERSONNEL : Admin + Manager (accès complet sur toutes les entités) ----------
Route::middleware(['auth', 'role:admin,manager'])->group(function () {

    // Tableau de bord général (statistiques)
    Route::get('/tableau-de-bord', [TableauDeBordController::class, 'index'])->name('tableau-de-bord');

    // Gestion des opérateurs externes : CRUD complet généré par Route::resource
    // (index, create, store, edit, update, destroy — pas de page "show" nécessaire)
    Route::resource('operateurs', OperateurController::class)->except('show');

    // Gestion du réseau local (AS) : configuration des paramètres de l'AS
    Route::get('/configuration-as', [ConfigurationAsController::class, 'edit'])->name('configuration-as.edit');
    Route::put('/configuration-as', [ConfigurationAsController::class, 'update'])->name('configuration-as.update');

    // Sessions BGP : CRUD complet + fiche détaillée (préfixes reçus/annoncés)
    Route::resource('sessions-bgp', SessionBgpController::class);
    // Ajout / suppression de préfixes depuis la fiche d'une session
    Route::post('/sessions-bgp/{sessions_bgp}/prefixes', [SessionBgpController::class, 'ajouterPrefixe'])
        ->name('sessions-bgp.prefixes.store');
    Route::delete('/sessions-bgp/{sessions_bgp}/prefixes/{prefixe}', [SessionBgpController::class, 'supprimerPrefixe'])
        ->name('sessions-bgp.prefixes.destroy');

    // Gestion des technologies d'accès : CRUD + fiche (équipements par technologie)
    // [FIX BINDING] Sans mapping, Laravel nomme le paramètre « {technology} » (singulier anglais)
    // alors que le contrôleur attend « $technologie » (français) : la liaison de modèle échouait
    // (modèle vide → fiche vide, édition en erreur 500, suppression sans effet). On force le nom.
    Route::resource('technologies', TechnologieController::class)
        ->parameters(['technologies' => 'technologie']);

    // Gestion des équipements : CRUD + fiche détaillée (interfaces du backbone)
    Route::resource('equipements', EquipementController::class);
    // [BACKBONE] Ajout / suppression d'interfaces (côté international ou côté accès) depuis la fiche
    Route::post('/equipements/{equipement}/interfaces', [EquipementController::class, 'ajouterInterface'])
        ->name('equipements.interfaces.store');
    Route::delete('/equipements/{equipement}/interfaces/{interface}', [EquipementController::class, 'supprimerInterface'])
        ->name('equipements.interfaces.destroy');

    // Gestion des plages IP : CRUD + fiche détaillée (nombre d'adresses du bloc)
    Route::resource('plages-ip', PlageIpController::class);

    // [CAPACITY PLANNING] Gestion de la bande passante : CRUD des allocations
    // (le paramètre est nommé "allocation" pour la liaison de modèle implicite)
    Route::resource('allocations', AllocationRessourceController::class)
        ->except('show')->parameters(['allocations' => 'allocation']);

    // [RECHERCHE GLOBALE] Barre de recherche multicritères (IP / CIDR / ASN / texte)
    Route::get('/recherche', [RechercheGlobaleController::class, 'rechercher'])->name('recherche');

    // Gestion des clients : CRUD + fiche détaillée (plages, équipements)
    Route::resource('clients', ClientController::class);
    // Affectation rapide d'une plage IP disponible depuis la fiche du client
    Route::post('/clients/{client}/affecter-plage', [ClientController::class, 'affecterPlage'])
        ->name('clients.affecter-plage');

    // [EXPORT] Formulaire dédié : ajuster les informations PUIS télécharger la fiche client (PDF/CSV)
    // sans modifier la fiche officielle en base (cas "modifier avant de télécharger").
    Route::get('/clients/{client}/exporter', [ExportClientController::class, 'formulaire'])
        ->name('clients.exporter.formulaire');
    Route::post('/clients/{client}/exporter', [ExportClientController::class, 'generer'])
        ->name('clients.exporter.generer');

    // [AUDIT] Journal des connexions et des modifications effectuées par le personnel.
    Route::get('/journal-audit', [JournalAuditController::class, 'index'])->name('journal-audit.index');
});

// ---------- Zone ADMIN SEUL : purge du journal d'audit ----------
// [SÉCURITÉ] Un journal que tout le monde peut effacer ne vaut plus rien comme preuve.
// La suppression est donc réservée à l'administrateur ; le manager peut seulement consulter.
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::delete('/journal-audit/purger', [JournalAuditController::class, 'purger'])
        ->name('journal-audit.purger');
    Route::delete('/journal-audit/{entree}', [JournalAuditController::class, 'destroy'])
        ->name('journal-audit.destroy');
});

// ---------- Zone CLIENT : lecture seule sur son propre périmètre ----------
Route::middleware(['auth', 'role:client'])->group(function () {
    // Portail unique : plages IP, technologies et équipements DU client connecté
    Route::get('/portail', [PortailClientController::class, 'accueil'])->name('portail.accueil');

    // [EXPORT] Téléchargement DIRECT de sa propre fiche (PDF ou CSV), sans formulaire de modification.
    Route::get('/portail/exporter/{format}', [ExportClientController::class, 'telechargerPortail'])
        ->whereIn('format', ['pdf', 'csv'])
        ->name('portail.exporter');
});
