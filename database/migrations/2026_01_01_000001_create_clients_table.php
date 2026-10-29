<?php
// Migration : création de la table "clients"
// Un client est une entité externe (entreprise ou particulier) à qui l'on affecte des ressources réseau.

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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();                                   // Clé primaire auto-incrémentée (bigint)
            $table->string('nom');                          // Raison sociale ou nom du client
            $table->string('email')->unique();              // Email de contact, unique dans la base
            $table->string('telephone')->nullable();        // Téléphone (facultatif)
            $table->string('adresse')->nullable();          // Adresse physique (facultatif)
            $table->timestamps();                           // Colonnes created_at / updated_at automatiques
        });
    }

    /**
     * Exécutée lors de "php artisan migrate:rollback" : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');                    // Suppression propre si la table existe
    }
};
