<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('details_planning_vertical', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_id')->constrained('plannings')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('horaire_id')->constrained('horaires_planning');
            $table->timestamps();

            $table->index('date');
            $table->unique(['planning_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('details_planning_vertical');
    }
};
