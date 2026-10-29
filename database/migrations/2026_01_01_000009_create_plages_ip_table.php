<?php
// Migration : création de la table "plages_ip" — cœur de l'IPAM.
// Une plage IP (bloc CIDR) peut être associée à une technologie et affectée à un client.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;              // Pour exécuter du SQL brut spécifique à PostgreSQL
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plages_ip', function (Blueprint $table) {
            $table->id();                                   // Clé primaire
            $table->string('reseau');                       // Bloc réseau au format CIDR (ex : 192.168.10.0/24)
            $table->string('description')->nullable();      // Usage prévu de la plage
            // Statut métier : "disponible" (libre) ou "utilisee" (affectée à un client)
            $table->enum('statut', ['disponible', 'utilisee'])->default('disponible');
            // Technologie de rattachement (cas "Associer une plage IP à une technologie")
            $table->foreignId('technologie_id')->nullable()->constrained('technologies')->nullOnDelete();
            // Client affecté (cas "Associer une plage IP à un client") ; null = plage libre
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            // [SOUS-PLAGES] Plage mère dont cette plage est issue par subdivision ; null = plage de tête.
            // cascadeOnDelete : supprimer la plage mère supprime aussi ses sous-plages.
            $table->foreignId('parent_id')->nullable()->constrained('plages_ip')->cascadeOnDelete();
            $table->timestamps();                           // created_at / updated_at
            $table->unique('reseau');                       // Une même plage ne peut pas exister deux fois
        });

        // Optimisation PostgreSQL : on convertit la colonne "reseau" vers le type natif "cidr".
        // Ce type valide le format réseau directement au niveau de la base de données.
        if (DB::getDriverName() === 'pgsql') {              // On ne le fait que si le SGBD est PostgreSQL
            DB::statement('ALTER TABLE plages_ip ALTER COLUMN reseau TYPE cidr USING reseau::cidr');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plages_ip');
    }
};
