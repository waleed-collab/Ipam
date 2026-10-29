<?php
// Migration : ajout de la colonne "capacite_totale" à la table "sessions_bgp".
// [CAPACITY PLANNING] Représente le débit physique global (en Mbps) fourni par
// l'opérateur sur cette session. Sert de plafond à la somme des bandes passantes allouées.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sessions_bgp', function (Blueprint $table) {
            // Débit total en Mbps ; nullable pour ne pas casser les sessions existantes (aucune régression)
            $table->unsignedInteger('capacite_totale')->nullable()->after('etat')
                  ->comment('Débit physique global fourni par l\'opérateur (Mbps)');
        });
    }

    public function down(): void
    {
        Schema::table('sessions_bgp', function (Blueprint $table) {
            $table->dropColumn('capacite_totale');          // Retrait de la colonne au rollback
        });
    }
};
