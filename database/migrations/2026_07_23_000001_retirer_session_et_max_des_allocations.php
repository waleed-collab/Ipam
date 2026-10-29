<?php
// Migration : retire de la table "allocation_ressources" les deux champs abandonnés
//   - session_bgp_id   (lien vers la session BGP source)
//   - bande_passante_max (débit crête / burst)
// La contrainte d'unicité, qui portait sur (client_id, technologie_id, session_bgp_id),
// est redéfinie sur (client_id, technologie_id).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allocation_ressources', function (Blueprint $table) {
            // 1) L'index d'unicité référence session_bgp_id : on le supprime d'abord.
            $table->dropUnique('allocation_unique');
            // 2) Suppression de la clé étrangère + colonne session_bgp_id.
            $table->dropConstrainedForeignId('session_bgp_id');
            // 3) Suppression du débit crête.
            $table->dropColumn('bande_passante_max');
            // 4) Nouvelle règle d'unicité : une offre par couple client / technologie.
            $table->unique(['client_id', 'technologie_id'], 'allocation_unique');
        });
    }

    public function down(): void
    {
        Schema::table('allocation_ressources', function (Blueprint $table) {
            $table->dropUnique('allocation_unique');
            $table->foreignId('session_bgp_id')->nullable()->after('technologie_id')
                  ->constrained('sessions_bgp')->nullOnDelete();
            $table->unsignedInteger('bande_passante_max')->nullable()->after('bande_passante_souscrite');
            $table->unique(['client_id', 'technologie_id', 'session_bgp_id'], 'allocation_unique');
        });
    }
};
