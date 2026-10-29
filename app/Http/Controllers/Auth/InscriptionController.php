<?php
// Contrôleur : InscriptionController
// [AUTH] Page d'inscription RÉSERVÉE AUX CLIENTS : crée simultanément
// la fiche client (table "clients") et le compte utilisateur (rôle "client").

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class InscriptionController extends Controller
{
    /**
     * Affiche le formulaire d'inscription (nom, prénom, entreprise, email, téléphone, mot de passe).
     */
    public function afficherFormulaire()
    {
        return view('auth.register');                       // Vue moderne dédiée à l'inscription
    }

    /**
     * Traite l'inscription : validation complète, création de la fiche client
     * + du compte utilisateur, puis connexion automatique vers le portail.
     */
    public function inscrire(Request $request)
    {
        // ---------- Validation Laravel complète ----------
        $donnees = $request->validate([
            'nom'        => ['required', 'string', 'max:100'],              // Nom de famille obligatoire
            'prenom'     => ['required', 'string', 'max:100'],              // Prénom obligatoire
            'entreprise' => ['nullable', 'string', 'max:150'],              // Entreprise facultative
            // Email unique à la fois côté comptes ET côté fiches clients
            'email'      => ['required', 'email', 'max:255', 'unique:users,email', 'unique:clients,email'],
            'telephone'  => ['required', 'string', 'max:30'],               // Téléphone obligatoire
            // Mot de passe : min. 8 caractères, lettres + chiffres, avec confirmation obligatoire
            'password'   => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            // Messages d'erreur personnalisés en français
            'email.unique'       => 'Cette adresse email est déjà utilisée.',
            'password.confirmed' => 'La confirmation ne correspond pas au mot de passe.',
        ]);

        // ---------- Création atomique fiche client + compte (transaction) ----------
        $utilisateur = DB::transaction(function () use ($donnees) {
            // 1. La fiche client : son nom est l'entreprise si fournie, sinon "Prénom Nom"
            $client = Client::create([
                'nom'       => $donnees['entreprise'] ?: ($donnees['prenom'] . ' ' . $donnees['nom']),
                'email'     => $donnees['email'],
                'telephone' => $donnees['telephone'],
            ]);

            // 2. Le compte utilisateur, TOUJOURS avec le rôle "client" (jamais admin/manager ici)
            return User::create([
                'name'      => $donnees['prenom'] . ' ' . $donnees['nom'],  // Nom affiché
                'email'     => $donnees['email'],
                'password'  => $donnees['password'],        // Haché automatiquement (cast "hashed")
                'role'      => 'client',                    // Rôle imposé : sécurité de la matrice des droits
                'client_id' => $client->id,                 // Rattachement à sa fiche (son périmètre)
            ]);
        });

        Auth::login($utilisateur);                          // Connexion automatique après inscription
        $request->session()->regenerate();                  // Nouvel ID de session (anti-fixation)

        // Redirection vers le portail client avec un message de bienvenue
        return redirect()->route('portail.accueil')->with('succes', 'Bienvenue ! Votre compte client a été créé.');
    }
}
