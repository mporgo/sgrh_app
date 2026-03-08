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
        Schema::create('avantages', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');                  // Voiture de fonction, Logement, etc.
            $table->text('description')->nullable();
            $table->decimal('valeur', 10, 2)->default(0); // valeur mensuelle estimée
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avantages');
    }
};
