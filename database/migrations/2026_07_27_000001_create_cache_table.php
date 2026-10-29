<?php
// Migration : tables du CACHE applicatif.
// [FIX] Le fichier .env déclare CACHE_STORE=database, mais la table correspondante n'avait
// jamais été créée : toute utilisation du cache échouait en erreur 500. C'est notamment le cas
// de la limitation anti-force brute du login (middleware "throttle"), qui stocke le compteur
// de tentatives dans le cache.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée les deux tables standard du pilote de cache "database".
     */
    public function up(): void
    {
        // Table principale : une entrée = une clé de cache et sa date d'expiration.
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();               // Clé unique de l'entrée
            $table->mediumText('value');                    // Valeur sérialisée
            $table->integer('expiration');                  // Horodatage d'expiration (timestamp)
        });

        // Table des verrous : évite que deux processus écrivent la même clé simultanément.
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();               // Clé verrouillée
            $table->string('owner');                        // Propriétaire du verrou
            $table->integer('expiration');                  // Expiration automatique du verrou
        });
    }

    /**
     * Suppression des tables (retour arrière).
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
