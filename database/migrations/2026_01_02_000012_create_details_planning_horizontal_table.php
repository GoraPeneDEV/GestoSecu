<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('details_planning_horizontal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_id')->constrained('plannings')->cascadeOnDelete();
            $table->enum('jour_semaine', ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche']);
            $table->foreignId('horaire_id')->constrained('horaires_planning');
            $table->timestamps();

            $table->index(['planning_id', 'jour_semaine']);
            $table->unique(['planning_id', 'jour_semaine']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('details_planning_horizontal');
    }
};
