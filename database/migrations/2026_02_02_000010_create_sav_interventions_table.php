<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sav_interventions', function (Blueprint $table) {
            $table->id();
            $table->string('numero_intervention')->unique();
            $table->enum('type', ['ponctuelle', 'maintenance_prevue']);
            $table->foreignId('maintenance_id')->nullable()->constrained('maintenances')->nullOnDelete();
            $table->foreignId('contrat_id')->nullable()->constrained('contrats')->nullOnDelete();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('technicien_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('date_intervention');
            $table->text('recommandations_generales')->nullable();
            $table->json('photos')->nullable();
            $table->enum('statut', ['brouillon', 'termine', 'annule'])->default('brouillon');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sav_interventions');
    }
};
