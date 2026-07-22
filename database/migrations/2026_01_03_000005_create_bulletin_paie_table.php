<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletin_paie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employe')->cascadeOnDelete();
            $table->integer('mois');
            $table->integer('annee');
            $table->string('numero_bulletin', 50)->unique();

            $table->decimal('salaire_base', 12, 2)->default(0);
            $table->decimal('total_gains', 12, 2)->default(0);
            $table->decimal('total_heures_sup', 12, 2)->default(0);
            $table->decimal('salaire_brut', 12, 2)->default(0);

            $table->decimal('cotisation_ipres', 12, 2)->default(0);
            $table->decimal('cotisation_css', 12, 2)->default(0);
            $table->decimal('cotisation_ipm', 12, 2)->default(0);
            $table->decimal('total_cotisations_salariales', 12, 2)->default(0);

            $table->decimal('cotisation_patronale_ipres', 12, 2)->default(0);
            $table->decimal('cotisation_patronale_css', 12, 2)->default(0);
            $table->decimal('cotisation_patronale_ipm', 12, 2)->default(0);
            $table->decimal('total_cotisations_patronales', 12, 2)->default(0);

            $table->decimal('salaire_net_imposable', 12, 2)->default(0);
            $table->decimal('trimf', 12, 2)->default(0);
            $table->decimal('cfce', 12, 2)->default(0);
            $table->decimal('impot_revenu', 12, 2)->default(0);

            $table->decimal('total_autres_retenues', 12, 2)->default(0);

            $table->decimal('salaire_net_a_payer', 12, 2)->default(0);

            $table->decimal('cumul_brut_annuel', 12, 2)->default(0);
            $table->decimal('cumul_net_annuel', 12, 2)->default(0);
            $table->decimal('cumul_ir_annuel', 12, 2)->default(0);

            $table->enum('statut', ['brouillon', 'valide', 'envoye', 'archive'])->default('brouillon');
            $table->timestamp('date_generation')->nullable();
            $table->foreignId('genere_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_validation')->nullable();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_envoi')->nullable();
            $table->string('pdf_path')->nullable();

            $table->timestamps();

            $table->unique(['employe_id', 'mois', 'annee'], 'unique_bulletin_employe_mois');
            $table->index(['mois', 'annee']);
            $table->index('statut');
            $table->index('numero_bulletin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_paie');
    }
};
