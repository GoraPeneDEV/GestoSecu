<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ronde_sup', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_ronde_sup_id')->constrained('planning_ronde_sup');
            $table->foreignId('agent_id')->constrained('employe');
            $table->dateTime('date_debut');
            $table->dateTime('date_fin')->nullable();
            $table->enum('statut', ['en_cours', 'terminee'])->default('en_cours');
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ronde_sup');
    }
};
