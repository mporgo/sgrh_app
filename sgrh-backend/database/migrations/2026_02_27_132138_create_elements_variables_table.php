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
        Schema::create('elements_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paie_id')->constrained('paies')->cascadeOnDelete();
            $table->string('libelle');
            $table->enum('type', ['prime', 'deduction', 'avantage']);
            $table->decimal('montant', 10, 2);
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elements_variables');
    }
};
