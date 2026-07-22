<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garanties', function (Blueprint $table) {
            $table->id();
            $table->string('numero_garantie')->unique();

            $table->foreignId('contrat_id')->nullable()->constrained();
            $table->foreignId('client_id')->constrained();

            $table->enum('type', ['main_oeuvre', 'pieces', 'totale']);
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('duree_mois');

            $table->text('conditions')->nullable();
            $table->text('exclusions')->nullable();

            $table->enum('statut', ['active', 'expiree', 'resiliee', 'en_reclamation'])->default('active');
            $table->integer('nombre_reclamations')->default(0);

            $table->boolean('alerte_30_jours_envoyee')->default(false);
            $table->boolean('alerte_7_jours_envoyee')->default(false);

            $table->timestamps();

            $table->index(['client_id', 'statut']);
            $table->index('date_fin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garanties');
    }
};
