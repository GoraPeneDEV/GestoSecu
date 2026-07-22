<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plannings_ronde', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('site_id')->constrained('sites');
            $table->enum('frequence', ['quotidienne', 'hebdomadaire', 'mensuelle']);
            $table->time('heure_debut');
            $table->integer('duree_estimee')->comment('en minutes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plannings_ronde');
    }
};
