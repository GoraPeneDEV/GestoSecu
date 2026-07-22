<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletin_ligne', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulletin_paie_id')->constrained('bulletin_paie')->cascadeOnDelete();
            $table->foreignId('element_paie_id')->nullable()->constrained('element_paie')->nullOnDelete();

            $table->string('code_element', 20);
            $table->string('libelle');
            $table->enum('type', ['gain', 'retenue', 'cotisation_salariale', 'cotisation_patronale', 'information']);

            $table->decimal('base_calcul', 12, 2)->nullable();
            $table->decimal('taux', 5, 2)->nullable();
            $table->decimal('nombre', 8, 2)->nullable();
            $table->decimal('montant', 12, 2)->default(0);

            $table->integer('ordre_affichage')->default(0);

            $table->timestamps();

            $table->index('bulletin_paie_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_ligne');
    }
};
