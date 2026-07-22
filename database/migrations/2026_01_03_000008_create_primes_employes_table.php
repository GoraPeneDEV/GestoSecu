<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('primes_employes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employe')->cascadeOnDelete();
            $table->foreignId('element_paie_id')->nullable()->constrained('element_paie')->nullOnDelete();

            $table->string('type_prime')->comment('transport, panier, anciennete, responsabilite, performance, projet, 13eme_mois, autre');
            $table->string('libelle');
            $table->decimal('montant', 10, 2)->nullable();
            $table->decimal('pourcentage', 5, 2)->nullable();

            $table->boolean('est_permanente')->default(true);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->boolean('est_active')->default(true);

            $table->boolean('est_soumise_ipres')->default(true);
            $table->boolean('est_soumise_css')->default(true);
            $table->boolean('est_soumise_ipm')->default(true);
            $table->boolean('est_soumise_ir')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['employe_id', 'est_active']);
            $table->index(['type_prime', 'est_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('primes_employes');
    }
};
