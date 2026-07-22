<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immobilisation_mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('immobilisation_id')->constrained('immobilisations')->cascadeOnDelete();
            $table->enum('type_mouvement', [
                'creation',
                'affectation',
                'transfert_site',
                'transfert_employe',
                'retour_stock',
                'reparation_debut',
                'reparation_fin',
                'cession',
                'reforme',
                'inventaire',
            ]);

            $table->foreignId('ancien_employe_id')->nullable()->constrained('employe');
            $table->foreignId('ancien_site_id')->nullable()->constrained('immobilisation_sites');

            $table->foreignId('nouvel_employe_id')->nullable()->constrained('employe');
            $table->foreignId('nouveau_site_id')->nullable()->constrained('immobilisation_sites');

            $table->text('motif')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('immobilisation_id');
            $table->index('type_mouvement');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immobilisation_mouvements');
    }
};
