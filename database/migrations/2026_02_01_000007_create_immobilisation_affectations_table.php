<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immobilisation_affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('immobilisation_id')->constrained('immobilisations')->cascadeOnDelete();

            $table->foreignId('employe_id')->constrained('employe');

            $table->date('date_affectation');
            $table->date('date_fin_prevue')->nullable();
            $table->date('date_fin_reelle')->nullable();

            $table->enum('type_affectation', ['dotation', 'pret', 'service', 'gardien', 'mission'])->default('dotation');

            $table->foreignId('dotation_id')->nullable()->constrained('dotations');

            $table->enum('etat_retour', ['bon', 'abime', 'hors_service', 'perdu'])->nullable();
            $table->text('observation_retour')->nullable();

            $table->json('documents')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('immobilisation_id', 'immob_affect_immob_id_idx');
            $table->index('employe_id', 'immob_affect_emp_id_idx');
            $table->index('date_affectation', 'immob_affect_date_idx');
            $table->index(['immobilisation_id', 'date_affectation'], 'immob_affect_immob_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immobilisation_affectations');
    }
};
