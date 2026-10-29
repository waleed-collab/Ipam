<?php
// Migration : création de la table pivot "allocation_ressources".
// [CAPACITY PLANNING] Une allocation relie un CLIENT à une TECHNOLOGIE (et
// éventuellement à la SESSION BGP qui fournit la capacité) avec :
//   - bande_passante_souscrite : le débit commercialisé au client (Mbps)
//   - bande_passante_max      : le débit crête autorisé / burst (Mbps)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocation_ressources', function (Blueprint $table) {
            $table->id();                                   // Clé primaire
            // Client bénéficiaire : la suppression du client supprime ses allocations
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            // Technologie d'accès utilisée : impose sa limite "debit_max_supporte"
            $table->foreignId('technologie_id')->constrained('technologies')->cascadeOnDelete();
            // Session BGP source de capacité (facultatif) : impose son plafond "capacite_totale"
            $table->foreignId('session_bgp_id')->nullable()->constrained('sessions_bgp')->nullOnDelete();
            $table->unsignedInteger('bande_passante_souscrite');            // Débit souscrit (Mbps)
            $table->unsignedInteger('bande_passante_max')->nullable();      // Débit crête autorisé (Mbps)
            $table->string('description')->nullable();      // Libellé de l'offre (ex : "Fibre Pro 100M")
            $table->timestamps();                           // created_at / updated_at
            // Un même couple client/technologie/session ne peut exister qu'une fois
            $table->unique(['client_id', 'technologie_id', 'session_bgp_id'], 'allocation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocation_ressources');
    }
};
