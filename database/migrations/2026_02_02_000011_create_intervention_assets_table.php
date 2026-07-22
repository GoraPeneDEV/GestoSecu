<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intervention_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sav_intervention_id')->constrained('sav_interventions')->cascadeOnDelete();
            $table->foreignId('client_asset_id')->constrained('client_assets')->cascadeOnDelete();
            $table->text('actions_faites')->nullable();
            $table->text('recommandation_specifique')->nullable();
            $table->string('statut_apres')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_assets');
    }
};
