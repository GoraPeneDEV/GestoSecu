<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immobilisations', function (Blueprint $table) {
            $table->id();

            $table->string('code_interne')->unique();
            $table->string('designation');
            $table->text('description')->nullable();
            $table->string('numero_serie')->nullable();

            $table->foreignId('categorie_id')->constrained('immobilisation_categories');

            $table->foreignId('site_id')->constrained('immobilisation_sites');
            $table->foreignId('emplacement_id')->nullable()->constrained('immobilisation_emplacements');

            $table->date('date_acquisition');
            $table->decimal('valeur_acquisition', 15, 2);
            $table->string('numero_facture')->nullable();

            $table->foreignId('article_id')->nullable()->constrained('articles');

            $table->enum('statut', [
                'en_stock',
                'affecte',
                'en_reparation',
                'en_transit',
                'cede',
                'reforme',
                'perdu',
            ])->default('en_stock');

            $table->foreignId('employe_id')->nullable()->constrained('employe');
            $table->date('date_affectation')->nullable();

            $table->enum('methode_amortissement', ['lineaire', 'degressif', 'variable'])->default('lineaire');
            $table->decimal('taux_amortissement', 5, 2)->nullable();
            $table->integer('duree_amortissement_annees')->nullable();
            $table->date('date_debut_amortissement');
            $table->decimal('valeur_residuelle', 15, 2)->default(0);
            $table->decimal('valeur_nette_comptable', 15, 2)->nullable();

            $table->string('qr_token')->unique();
            $table->string('qr_code_path')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index('employe_id');
            $table->index('categorie_id');
            $table->index('site_id');
            $table->index(['statut', 'employe_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immobilisations');
    }
};
