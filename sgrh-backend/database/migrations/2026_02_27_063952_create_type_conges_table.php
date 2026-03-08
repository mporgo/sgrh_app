<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('type_conges', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');                          // Congés payés, RTT, Maladie...
            $table->integer('jours_annuels')->default(0);       // Dotation annuelle
            $table->boolean('reportable')->default(false);      // Report sur année suivante
            $table->boolean('justificatif_requis')->default(false);
            $table->string('couleur', 7)->default('#2e75b6');   // Couleur calendrier
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_conges');
    }
};
