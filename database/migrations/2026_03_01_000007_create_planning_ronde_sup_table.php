<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_ronde_sup', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->enum('frequence', ['quotidienne', 'hebdomadaire', 'mensuelle']);
            $table->time('heure_debut');
            $table->integer('duree_estimee');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_ronde_sup');
    }
};
