<?php
// Migration : création de la table "users"
// Chaque utilisateur possède un rôle (admin, manager ou client) qui détermine ses droits.
// Un utilisateur de rôle "client" est rattaché à une fiche de la table "clients" (son périmètre unique).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();                                   // Clé primaire
            $table->string('name');                         // Nom affiché de l'utilisateur
            $table->string('email')->unique();              // Identifiant de connexion (unique)
            $table->string('password');                     // Mot de passe haché (bcrypt)
            // Rôle applicatif : contrôle TOUTES les autorisations (cf. tableau "Acteurs et Droits d'Accès")
            $table->enum('role', ['admin', 'manager', 'client'])->default('client');
            // Lien vers la fiche client : obligatoire seulement pour le rôle "client".
            // nullOnDelete : si le client est supprimé, le compte perd simplement son rattachement.
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->rememberToken();                        // Jeton "se souvenir de moi" de Laravel
            $table->timestamps();                           // created_at / updated_at
        });

        // Table standard Laravel pour stocker les sessions HTTP en base (driver "database")
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();                // Identifiant de session
            $table->foreignId('user_id')->nullable()->index(); // Utilisateur connecté (si authentifié)
            $table->string('ip_address', 45)->nullable();   // Adresse IP du visiteur (IPv4 ou IPv6)
            $table->text('user_agent')->nullable();         // Navigateur utilisé
            $table->longText('payload');                    // Données sérialisées de la session
            $table->integer('last_activity')->index();      // Horodatage de la dernière activité
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');                   // On supprime d'abord les tables dépendantes
        Schema::dropIfExists('users');
    }
};
