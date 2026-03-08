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
        Schema::create('inscription_formations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained('formations')->cascadeOnDelete();
            $table->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
            $table->enum('statut', ['en_attente', 'validee', 'refusee', 'terminee', 'abandonnee'])
                ->default('en_attente');
            $table->integer('note')->nullable();             // note obtenue /20
            $table->boolean('certificat_obtenu')->default(false);
            $table->text('commentaire')->nullable();
            $table->timestamp('inscrit_le')->nullable();
            $table->timestamps();

            $table->unique(['formation_id', 'employe_id']); // un employé ne s'inscrit qu'une fois
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscription_formations');
    }
};
