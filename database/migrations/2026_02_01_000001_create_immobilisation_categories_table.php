<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immobilisation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->enum('type_bien', ['corporel', 'incorporel', 'financier'])->default('corporel');
            $table->boolean('est_dotable')->default(false);
            $table->boolean('est_amortissable')->default(true);
            $table->enum('methode_amortissement_defaut', ['lineaire', 'degressif', 'variable'])->default('lineaire');
            $table->integer('duree_amortissement_defaut')->nullable();
            $table->decimal('taux_amortissement_defaut', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immobilisation_categories');
    }
};
