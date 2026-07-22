<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employe_paie_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employe')->cascadeOnDelete();

            // Informations salariales
            $table->decimal('salaire_base', 12, 2)->default(0);
            $table->decimal('sursalaire', 12, 2)->default(0)->comment('Prime de fonction, etc.');
            $table->string('categorie_professionnelle')->nullable()->comment('Cadre, Agent de maitrise, Employe, Ouvrier');
            $table->string('classification')->nullable();
            $table->integer('echelon')->nullable();
            $table->decimal('coefficient', 8, 2)->nullable();

            // Fiscalite personnelle
            $table->decimal('parts_fiscales', 3, 1)->default(1.0);
            $table->integer('nombre_epouses')->default(0);
            $table->integer('nombre_enfants_a_charge')->default(0);

            // Identifiants organismes
            $table->string('numero_ipres')->nullable()->unique();
            $table->string('numero_css')->nullable()->unique();
            $table->string('numero_ipm')->nullable();
            $table->string('numero_contribuable')->nullable();

            // Informations bancaires
            $table->string('banque_nom')->nullable();
            $table->string('banque_code')->nullable();
            $table->string('banque_guichet')->nullable();
            $table->string('numero_compte')->nullable();
            $table->string('cle_rib', 2)->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('domiciliation_bancaire')->nullable();

            // Primes fixes
            $table->decimal('indemnite_transport', 12, 2)->default(0);
            $table->decimal('prime_panier', 12, 2)->default(0);
            $table->decimal('indemnite_logement', 12, 2)->default(0);
            $table->decimal('prime_anciennete', 12, 2)->default(0);
            $table->decimal('prime_responsabilite', 12, 2)->default(0);

            // Informations professionnelles supplementaires
            $table->string('niveau', 10)->nullable();
            $table->unsignedInteger('indice')->nullable();
            $table->decimal('horaire_mensuel', 8, 2)->default(242.66);
            $table->string('mode_paiement', 50)->default('virement');

            // Conges
            $table->decimal('conges_acquis', 8, 2)->default(0);
            $table->decimal('conges_pris', 8, 2)->default(0);
            $table->decimal('conges_reste', 8, 2)->default(0);
            $table->decimal('repos_comp_acquis', 8, 2)->default(0);
            $table->decimal('repos_comp_pris', 8, 2)->default(0);
            $table->decimal('repos_comp_reste', 8, 2)->default(0);

            // Metadonnees
            $table->boolean('actif')->default(true);
            $table->date('date_derniere_augmentation')->nullable();
            $table->text('commentaire_paie')->nullable();

            $table->timestamps();

            $table->index('employe_id');
            $table->index('actif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employe_paie_data');
    }
};
