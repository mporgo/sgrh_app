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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
            $table->foreignId('evaluateur_id')->constrained('users');
            $table->date('date_evaluation');
            $table->date('date_prochaine')->nullable();
            $table->enum('type', ['annuelle', 'semestrielle', 'periode_essai', 'autre'])->default('annuelle');
            $table->enum('statut', ['planifiee', 'en_cours', 'terminee', 'annulee'])->default('planifiee');

            // Résultats
            $table->enum('note_globale', ['insuffisant', 'passable', 'bien', 'tres_bien', 'excellent'])->nullable();
            $table->integer('score')->nullable();   // score numérique /100 (optionnel)

            // Contenu
            $table->text('objectifs_fixes')->nullable();       // objectifs fixés
            $table->text('objectifs_atteints')->nullable();    // résultats de la période
            $table->text('points_forts')->nullable();
            $table->text('axes_amelioration')->nullable();
            $table->text('commentaire_evaluateur')->nullable();
            $table->text('commentaire_employe')->nullable();   // réponse de l'employé

            $table->boolean('signe_employe')->default(false);
            $table->boolean('signe_evaluateur')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
