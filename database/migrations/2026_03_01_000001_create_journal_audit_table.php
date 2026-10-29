<?php
// Migration : création de la table "journal_audit"
// Trace toutes les connexions/déconnexions ainsi que les modifications effectuées
// par le personnel (admin, manager) sur les ressources de l'application.

use Illuminate\Database\Migrations\Migration;   // Classe de base de toute migration Laravel
use Illuminate\Database\Schema\Blueprint;       // Permet de décrire la structure d'une table
use Illuminate\Support\Facades\Schema;          // Façade pour manipuler le schéma PostgreSQL

return new class extends Migration
{
    /**
     * Exécutée lors de "php artisan migrate" : crée la table.
     */
    public function up(): void
    {
        Schema::create('journal_audit', function (Blueprint $table) {
            $table->id();                                   // Clé primaire auto-incrémentée (bigint)
            // Référence vers l'utilisateur ; conservée à null si le compte est supprimé (nom figé ci-dessous).
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nom_utilisateur');              // Nom figé au moment de l'action (résiste à la suppression du compte)
            $table->string('role');                         // Rôle au moment de l'action : admin, manager, client
            $table->string('action');                       // connexion, deconnexion, creation, modification, suppression, telechargement
            $table->string('entite')->nullable();           // Type de ressource concernée (Client, PlageIp, Equipement...)
            $table->unsignedBigInteger('entite_id')->nullable(); // Identifiant de la ressource concernée
            $table->text('description')->nullable();        // Détail lisible de l'action effectuée
            $table->string('adresse_ip')->nullable();       // Adresse IP à l'origine de l'action
            $table->timestamp('created_at')->useCurrent();  // Horodatage de l'action (pas de updated_at : le journal est immuable)
        });
    }

    /**
     * Exécutée lors de "php artisan migrate:rollback" : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_audit');              // Suppression propre si la table existe
    }
};
