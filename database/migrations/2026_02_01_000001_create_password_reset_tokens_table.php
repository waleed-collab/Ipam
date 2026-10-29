<?php
// Migration : création de la table "password_reset_tokens"
// [AUDIT] Cette table standard de Laravel manquait : elle est indispensable au
// workflow "Mot de passe oublié" (stockage temporaire des jetons de réinitialisation).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();             // Email du compte demandant la réinitialisation
            $table->string('token');                        // Jeton haché envoyé dans le lien par email
            $table->timestamp('created_at')->nullable();    // Date de création (pour l'expiration du lien)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');      // Suppression lors d'un rollback
    }
};
