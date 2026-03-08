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
        Schema::create('paies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
            $table->integer('mois');                    // 1-12
            $table->integer('annee');
            $table->decimal('salaire_base', 10, 2)->default(0);
            $table->decimal('total_primes', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('total_avantages', 10, 2)->default(0);
            $table->decimal('net_a_payer', 10, 2)->default(0);
            $table->decimal('cotisation_cnss', 10, 2)->default(0);  // 5.5% salarié
            $table->decimal('impot_iuts', 10, 2)->default(0);       // IUTS Burkina
            $table->enum('statut', ['brouillon', 'valide', 'paye'])->default('brouillon');
            $table->date('date_paiement')->nullable();
            $table->string('reference')->unique();      // ex: BULL-2025-07-EMP001
            $table->text('notes')->nullable();
            $table->foreignId('genere_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employe_id', 'mois', 'annee']); // un bulletin par mois
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paies');
    }
};
