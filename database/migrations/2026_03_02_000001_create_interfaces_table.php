<?php
// Migration : création de la table "interfaces" — cadre "Local" du schéma d'architecture.
// L'équipement BACKBONE local porte des interfaces de deux types :
//  - "internationale" (relations externes) : nom, @IP, bande passante, neighbor (opérateur) —
//    l'AS local est lu dans configuration_as ;
//  - "acces" : nom, @IP, bande passante, équipement d'accès derrière et technologie d'accès.
// Les sessions BGP extérieures s'appuient sur une interface internationale (lien ajouté ici).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interfaces', function (Blueprint $table) {
            $table->id();                                   // Clé primaire
            // Équipement backbone propriétaire de l'interface : sa suppression emporte ses interfaces.
            $table->foreignId('equipement_id')->constrained('equipements')->cascadeOnDelete();
            $table->string('nom');                          // Nom de l'interface (ex : GigabitEthernet0/0/1)
            $table->string('adresse_ip')->nullable();       // Adresse IP portée par l'interface
            $table->unsignedInteger('bande_passante')->nullable(); // Bande passante du lien (Mbps)
            // Côté de l'interface : "internationale" (vers les opérateurs) ou "acces" (vers le réseau d'accès)
            $table->enum('type', ['internationale', 'acces']);
            // [CÔTÉ INTERNATIONAL] Neighbor : l'opérateur externe joint par cette interface.
            $table->foreignId('operateur_id')->nullable()->constrained('operateurs')->nullOnDelete();
            // [CÔTÉ ACCÈS] Équipement d'accès raccordé derrière cette interface.
            $table->foreignId('equipement_distant_id')->nullable()->constrained('equipements')->nullOnDelete();
            // [CÔTÉ ACCÈS] Technologie d'accès desservie derrière l'interface.
            $table->foreignId('technologie_id')->nullable()->constrained('technologies')->nullOnDelete();
            $table->timestamps();                           // created_at / updated_at
        });

        // Lien session BGP -> interface internationale utilisée (donne l'@ locale et la bande passante).
        Schema::table('sessions_bgp', function (Blueprint $table) {
            $table->foreignId('interface_id')->nullable()->constrained('interfaces')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // On retire d'abord la colonne dépendante, puis la table.
        Schema::table('sessions_bgp', function (Blueprint $table) {
            $table->dropConstrainedForeignId('interface_id');
        });
        Schema::dropIfExists('interfaces');
    }
};
