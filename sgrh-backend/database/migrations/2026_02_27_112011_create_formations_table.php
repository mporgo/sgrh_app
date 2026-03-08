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
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('formateur')->nullable();          // nom du formateur/organisme
            $table->enum('type', ['interne', 'externe', 'elearning'])->default('interne');
            $table->enum('statut', ['planifiee', 'en_cours', 'terminee', 'annulee'])->default('planifiee');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('duree_heures')->default(0);
            $table->integer('places_max')->nullable();        // null = illimité
            $table->decimal('cout', 10, 2)->default(0);
            $table->string('lieu')->nullable();
            $table->string('lien_elearning')->nullable();     // pour le type elearning
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
