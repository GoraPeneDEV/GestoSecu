<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_ronde_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_ronde_id')->constrained('plannings_ronde')->onDelete('cascade');
            $table->foreignId('point_controle_id')->constrained('point_controles');
            $table->integer('ordre')->comment('ordre de passage du point de contrôle');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_ronde_points');
    }
};
