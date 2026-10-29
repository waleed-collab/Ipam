<?php
// Migration : ajoute les PLAGES PRINCIPALES (préfixes alloués par le RIR) à la configuration.
// Ces blocs sont l'allocation reçue de l'organisme d'attribution (AFRINIC pour l'Afrique) :
// c'est à l'intérieur de ces plages que sont découpés les sous-blocs affectés aux clients.
// Le tableau de bord s'en sert pour calculer la capacité globale, la part utilisée et le restant.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuration_as', function (Blueprint $table) {
            // Ex : 102.64.0.0/19 — 2^(32-19) = 8 192 adresses au total
            $table->string('prefixe_ipv4')->nullable()->after('router_id');
            // Ex : 2001:db8::/32 — compté en sous-réseaux /64, unité usuelle en IPv6
            $table->string('prefixe_ipv6')->nullable()->after('prefixe_ipv4');
        });
    }

    public function down(): void
    {
        Schema::table('configuration_as', function (Blueprint $table) {
            $table->dropColumn(['prefixe_ipv4', 'prefixe_ipv6']);
        });
    }
};
