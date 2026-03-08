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
        Schema::create('conges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
            $table->foreignId('type_conge_id')->constrained('type_conges');
            $table->foreignId('valideur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('nombre_jours');                    // calculé automatiquement
            $table->text('commentaire')->nullable();
            $table->text('motif_refus')->nullable();
            $table->enum('statut', [
                'en_attente', 'valide', 'refuse', 'annule'
            ])->default('en_attente');
            $table->timestamp('valide_le')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conges');
    }
};
