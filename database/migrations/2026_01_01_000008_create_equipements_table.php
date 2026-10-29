<?php
// Migration : création de la table "equipements"
// Un équipement (routeur, switch, antenne, ONT...) est rattaché à une technologie d'accès
// et, éventuellement, au client chez qui il est installé.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipements', function (Blueprint $table) {
            $table->id();                                   // Clé primaire
            $table->string('nom');                          // Nom / hostname de l'équipement
            $table->string('type');                         // Type : routeur, switch, antenne, modem...
            // Rôle dans l'architecture : "backbone" = équipement local central (cadre "Local" du schéma)
            // qui porte les interfaces (côté international / côté accès) ; "acces" = équipement terrain.
            $table->enum('role', ['backbone', 'acces'])->default('acces');
            $table->string('adresse_ip')->nullable();       // IP de management de l'équipement (facultatif)
            // Technologie associée (cas "Associer un équipement à une technologie").
            // nullOnDelete : si la technologie est supprimée, l'équipement devient "non associé".
            $table->foreignId('technologie_id')->nullable()->constrained('technologies')->nullOnDelete();
            // Client chez qui l'équipement est déployé (cas "Visualiser les équipements rattachés à un client")
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->timestamps();                           // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipements');
    }
};
