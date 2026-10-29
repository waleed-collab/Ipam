<?php
// Migration : création de la table "prefixes_bgp"
// Stocke les préfixes réseau échangés sur une session BGP :
//  - "recu"    : préfixe appris depuis le voisin
//  - "annonce" : préfixe que nous annonçons au voisin
// (cas d'utilisation "Consulter les préfixes reçus et annoncés")

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prefixes_bgp', function (Blueprint $table) {
            $table->id();                                   // Clé primaire
            // Session BGP à laquelle appartient le préfixe ; supprimé avec la session
            $table->foreignId('session_bgp_id')->constrained('sessions_bgp')->cascadeOnDelete();
            $table->string('prefixe');                      // Préfixe au format CIDR (ex : 41.207.160.0/19)
            $table->enum('type', ['recu', 'annonce']);      // Sens du préfixe (reçu ou annoncé)
            $table->timestamps();                           // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prefixes_bgp');
    }
};
