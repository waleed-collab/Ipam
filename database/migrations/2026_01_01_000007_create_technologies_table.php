<?php
// Migration : création de la table "technologies"
// Une technologie d'accès est un moyen de raccordement des clients : Fibre, FH (faisceau hertzien), VSAT, 4G, etc.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technologies', function (Blueprint $table) {
            $table->id();                                   // Clé primaire
            $table->string('nom')->unique();                // Nom de la technologie (unique : "Fibre optique"...)
            $table->text('description')->nullable();        // Description libre
            $table->timestamps();                           // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technologies');
    }
};
