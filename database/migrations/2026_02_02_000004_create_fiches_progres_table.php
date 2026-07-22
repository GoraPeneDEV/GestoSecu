<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiches_progres', function (Blueprint $table) {
            $table->id();
            $table->string('numero_fiche')->unique();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('contrat_id')->nullable()->constrained();
            $table->foreignId('contact_id')->nullable()->constrained('client_contacts');

            $table->enum('type', ['amelioration', 'reclamation', 'incident', 'dysfonctionnement', 'non_conformite']);

            $table->enum('processus_concerne', [
                'gardiennage',
                'securite_electronique',
                'securite_incendie',
                'monetique',
                'nettoyage',
                'formation',
                'solution_it',
                'comptabilite',
                'commercial',
                'accueil',
            ]);

            $table->string('objet');
            $table->text('constat_client');
            $table->text('cause_analyse')->nullable();

            $table->enum('statut', [
                'nouveau',
                'analyse_en_cours',
                'plan_action_etabli',
                'actions_en_cours',
                'evaluation',
                'cloture',
                'non_fonde',
            ])->default('nouveau');

            $table->json('analyse_5m')->nullable();

            $table->boolean('efficacite_actions')->nullable();
            $table->text('commentaire_efficacite')->nullable();
            $table->boolean('redemarrage_analyse')->default(false);

            $table->foreignId('pilote_processus_id')->nullable()->constrained('users');
            $table->timestamp('date_validation_pilote')->nullable();
            $table->foreignId('responsable_qualite_id')->nullable()->constrained('users');
            $table->timestamp('date_cloture')->nullable();

            $table->foreignId('cree_par')->constrained('users');
            $table->timestamp('date_reception')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index(['type', 'statut']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiches_progres');
    }
};
