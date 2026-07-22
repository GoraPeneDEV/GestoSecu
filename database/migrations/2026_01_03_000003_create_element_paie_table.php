<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('element_paie', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('libelle');
            $table->enum('type', ['gain', 'retenue', 'cotisation_salariale', 'cotisation_patronale']);

            $table->enum('mode_calcul', ['fixe', 'pourcentage', 'formule'])->default('fixe');
            $table->decimal('valeur', 12, 2)->nullable();
            $table->string('formule_classe')->nullable();

            $table->boolean('soumis_ipres')->default(true);
            $table->boolean('soumis_css')->default(true);
            $table->boolean('soumis_ipm')->default(true);
            $table->boolean('soumis_ir')->default(true);
            $table->decimal('plafond_exoneration', 12, 2)->nullable();

            $table->integer('ordre_affichage')->default(0);
            $table->boolean('afficher_bulletin')->default(true);
            $table->boolean('actif')->default(true);

            $table->timestamps();

            $table->index('type');
            $table->index('actif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('element_paie');
    }
};
