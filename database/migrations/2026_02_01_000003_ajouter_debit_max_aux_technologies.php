<?php
// Migration : ajout de la colonne "debit_max_supporte" à la table "technologies".
// [CAPACITY PLANNING] Limite technique (en Mbps) qu'un client ne peut pas dépasser
// lorsqu'il souscrit de la bande passante sur cette technologie d'accès.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technologies', function (Blueprint $table) {
            // Limite technique en Mbps ; nullable = pas de limite définie (aucune régression)
            $table->unsignedInteger('debit_max_supporte')->nullable()->after('description')
                  ->comment('Limite technique de débit par client (Mbps)');
        });
    }

    public function down(): void
    {
        Schema::table('technologies', function (Blueprint $table) {
            $table->dropColumn('debit_max_supporte');       // Retrait de la colonne au rollback
        });
    }
};
