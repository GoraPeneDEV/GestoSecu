<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grilles_salariales', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->integer('annee_debut');
            $table->integer('annee_fin')->nullable();
            $table->boolean('est_active')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('est_active');
            $table->index(['annee_debut', 'annee_fin']);
        });

        Schema::create('categories_grilles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grille_id')->constrained('grilles_salariales')->cascadeOnDelete();
            $table->string('code', 50)->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->integer('ordre_affichage')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['grille_id', 'ordre_affichage']);
        });

        Schema::create('echelons_grilles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categorie_id')->constrained('categories_grilles')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('nom');
            $table->integer('niveau');
            $table->decimal('coefficient', 5, 2);
            $table->decimal('salaire_min', 10, 2);
            $table->decimal('salaire_max', 10, 2);
            $table->text('description')->nullable();
            $table->integer('ordre_affichage')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['categorie_id', 'ordre_affichage']);
            $table->index('niveau');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('echelons_grilles');
        Schema::dropIfExists('categories_grilles');
        Schema::dropIfExists('grilles_salariales');
    }
};
