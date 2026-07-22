<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiche_progres_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiche_progres_id')->constrained('fiches_progres')->cascadeOnDelete();
            $table->text('description');
            $table->foreignId('responsable_id')->constrained('users');
            $table->date('date_echeance');
            $table->enum('statut', ['non_demarree', 'en_cours', 'realisee', 'retardee'])->default('non_demarree');
            $table->date('date_realisation')->nullable();
            $table->text('preuves')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->index('fiche_progres_id');
            $table->index(['responsable_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiche_progres_actions');
    }
};
