<?php
// Migration : création de la table "configuration_as"
// Contient les paramètres du Système Autonome (AS) local : une seule ligne est utilisée en pratique.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuration_as', function (Blueprint $table) {
            $table->id();                                   // Clé primaire
            $table->unsignedBigInteger('asn_local');        // Numéro d'AS local (ex : 65001)
            $table->string('nom_reseau');                   // Nom du réseau local (ex : "IPAM-Backbone")
            $table->string('router_id')->nullable();        // Router-ID BGP (souvent une IPv4, ex : 10.0.0.1)
            $table->text('description')->nullable();        // Description libre de l'AS
            $table->timestamps();                           // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuration_as');
    }
};
