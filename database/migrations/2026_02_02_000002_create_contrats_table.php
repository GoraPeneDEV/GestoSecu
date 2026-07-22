<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->string('numero_contrat')->unique();
            $table->foreignId('client_id')->constrained();

            $table->enum('type', [
                'maintenance',
                'gardiennage',
                'securite_electronique',
                'securite_incendie',
                'monetique',
                'nettoyage',
                'it',
                'formation',
                'prestation_ponctuelle',
                'mixte',
            ]);

            $table->date('date_signature');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('statut', [
                'brouillon',
                'en_attente_signature',
                'actif',
                'suspendu',
                'resilie',
                'expire',
                'renouvele',
            ])->default('brouillon');

            $table->decimal('montant_total', 12, 2);
            $table->enum('frequence_paiement', ['mensuel', 'trimestriel', 'semestriel', 'annuel', 'unique'])->default('mensuel');
            $table->text('prestations_incluses')->nullable();

            $table->integer('delai_intervention_heures')->default(24);
            $table->integer('nombre_interventions_incluses')->nullable();
            $table->boolean('garantie_incluse')->default(false);
            $table->integer('duree_garantie_mois')->nullable();

            $table->boolean('renouvellement_auto')->default(false);
            $table->integer('preavis_renouvellement_jours')->default(30);

            $table->foreignId('responsable_sav_id')->nullable()->constrained('users');
            $table->foreignId('signataire_id')->nullable()->constrained('users');

            $table->string('fichier_contrat')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index('date_fin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
