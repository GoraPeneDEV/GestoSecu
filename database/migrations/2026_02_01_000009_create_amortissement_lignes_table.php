<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amortissement_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('immobilisation_id')->constrained('immobilisations')->cascadeOnDelete();
            $table->integer('annee_exercice');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('duree_jours');
            $table->decimal('montant_amortissement', 15, 2);
            $table->decimal('cumul_amortissement', 15, 2);
            $table->decimal('valeur_nette', 15, 2);
            $table->timestamps();

            $table->unique(['immobilisation_id', 'annee_exercice']);
            $table->index('annee_exercice');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amortissement_lignes');
    }
};
