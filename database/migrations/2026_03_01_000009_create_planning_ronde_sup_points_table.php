<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_ronde_sup_points', function (Blueprint $table) {
            $table->foreignId('planning_ronde_sup_id')->constrained('planning_ronde_sup');
            $table->foreignId('point_controle_sup_id')->constrained('points_controle_sup');
            $table->integer('ordre');
            $table->timestamps();
            $table->primary(['planning_ronde_sup_id', 'point_controle_sup_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_ronde_sup_points');
    }
};
