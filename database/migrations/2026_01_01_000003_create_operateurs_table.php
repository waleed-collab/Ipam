<?php
// Migration : création de la table "operateurs"
// Un opérateur externe est un fournisseur/pair réseau (transitaire, peer) avec qui l'on établit des sessions BGP.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operateurs', function (Blueprint $table) {
            $table->id();                                   // Clé primaire
            $table->string('nom');                          // Nom commercial de l'opérateur
            $table->unsignedBigInteger('asn');              // Numéro d'AS public de l'opérateur (ex : 3356)
            $table->string('contact')->nullable();          // Nom de la personne de contact (facultatif)
            $table->string('email_contact')->nullable();    // Email du NOC / contact technique
            $table->text('description')->nullable();        // Notes libres sur l'opérateur
            $table->timestamps();                           // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operateurs');
    }
};
