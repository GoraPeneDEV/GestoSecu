<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variable_paie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employe')->cascadeOnDelete();
            $table->integer('mois');
            $table->integer('annee');

            $table->decimal('jours_travailles', 5, 2)->default(0);
            $table->decimal('jours_absence_non_payee', 5, 2)->default(0);

            $table->decimal('heures_sup_15', 5, 2)->default(0);
            $table->decimal('heures_sup_40', 5, 2)->default(0);
            $table->decimal('heures_sup_60', 5, 2)->default(0);
            $table->decimal('heures_sup_100', 5, 2)->default(0);

            $table->decimal('prime_exceptionnelle', 12, 2)->default(0);
            $table->text('motif_prime_exceptionnelle')->nullable();

            $table->decimal('retenue_exceptionnelle', 12, 2)->default(0);
            $table->text('motif_retenue_exceptionnelle')->nullable();

            $table->decimal('montant_acompte', 12, 2)->default(0);
            $table->decimal('montant_avance', 12, 2)->default(0);

            $table->text('commentaire')->nullable();
            $table->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_saisie')->nullable();
            $table->boolean('validee')->default(false);
            $table->foreignId('validee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_validation')->nullable();
            $table->boolean('verrouillee')->default(false);

            $table->timestamps();

            $table->unique(['employe_id', 'mois', 'annee'], 'unique_employe_mois');
            $table->index(['mois', 'annee']);
            $table->index('validee');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variable_paie');
    }
};
