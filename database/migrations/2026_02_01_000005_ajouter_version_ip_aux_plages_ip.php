<?php
// Migration : ajout de la colonne "version_ip" à la table "plages_ip".
// [IPv4/IPv6] Permet de distinguer et de statistiquer séparément les deux protocoles.
// La colonne "reseau" reste au type natif PostgreSQL "cidr" (qui accepte déjà l'IPv6).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;              // Pour le rétro-remplissage en SQL natif
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plages_ip', function (Blueprint $table) {
            // Version du protocole : 4 ou 6 (petit entier suffisant)
            $table->unsignedTinyInteger('version_ip')->default(4)->after('reseau')
                  ->comment('Version du protocole IP : 4 ou 6');
        });

        // Rétro-remplissage automatique des plages existantes grâce à la fonction
        // PostgreSQL family() qui retourne 4 ou 6 selon le type d'adresse.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('UPDATE plages_ip SET version_ip = family(reseau)');
        }
    }

    public function down(): void
    {
        Schema::table('plages_ip', function (Blueprint $table) {
            $table->dropColumn('version_ip');               // Retrait de la colonne au rollback
        });
    }
};
