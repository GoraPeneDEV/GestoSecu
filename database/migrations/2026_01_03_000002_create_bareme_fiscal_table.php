<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bareme_fiscal', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('ipres_rg, ipres_cadre, css, ipm, ir, trimf, cfce');
            $table->integer('annee');

            $table->decimal('taux_salarial', 5, 2)->nullable();
            $table->decimal('taux_patronal', 5, 2)->nullable();
            $table->decimal('plafond', 12, 2)->nullable();

            $table->decimal('tranche_min', 12, 2)->nullable();
            $table->decimal('tranche_max', 12, 2)->nullable();
            $table->decimal('taux_ir', 5, 2)->nullable();

            $table->boolean('actif')->default(true);
            $table->text('description')->nullable();
            $table->string('reference_legale')->nullable();

            $table->timestamps();

            $table->index(['type', 'annee', 'actif']);
            $table->unique(['type', 'annee', 'tranche_min', 'tranche_max'], 'unique_bareme');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bareme_fiscal');
    }
};
