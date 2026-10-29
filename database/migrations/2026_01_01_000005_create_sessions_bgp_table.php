<?php
// Migration : création de la table "sessions_bgp"
// Une session BGP relie notre AS local à un opérateur externe (le voisin / peer).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions_bgp', function (Blueprint $table) {
            $table->id();                                   // Clé primaire
            // Opérateur associé à la session (cas d'utilisation "Associer un opérateur à une session BGP").
            // cascadeOnDelete : si l'opérateur est supprimé, ses sessions le sont aussi.
            $table->foreignId('operateur_id')->constrained('operateurs')->cascadeOnDelete();
            $table->string('ip_voisin');                    // Adresse IP du routeur voisin (peer)
            $table->unsignedBigInteger('asn_distant');      // Numéro d'AS du voisin
            // Adresse IP LOCALE de la session (notre côté du lien) — champ "@ local" du schéma.
            // L'ASN local, lui, vient de la table configuration_as (paramètre global de l'AS).
            $table->string('ip_locale')->nullable();
            // État de la session : "active" = établie, "inactive" = configurée mais coupée
            $table->enum('etat', ['active', 'inactive'])->default('inactive');
            $table->text('description')->nullable();        // Notes libres (type de lien, capacité, etc.)
            $table->timestamps();                           // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions_bgp');
    }
};
