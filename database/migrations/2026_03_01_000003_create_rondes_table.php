<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rondes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_ronde_id')->constrained('plannings_ronde');
            $table->foreignId('agent_id')->constrained('employe');
            $table->dateTime('date_debut');
            $table->dateTime('date_fin')->nullable();
            $table->enum('statut', ['en_cours', 'terminee', 'incomplete'])->default('en_cours');
            $table->text('commentaire')->nullable();
            $table->integer('steps')->nullable()->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rondes');
    }
};
