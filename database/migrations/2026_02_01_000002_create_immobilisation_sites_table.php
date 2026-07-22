<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immobilisation_sites', function (Blueprint $table) {
            $table->id();
            $table->string('code_site')->unique();
            $table->string('libelle');
            $table->enum('type', ['siege', 'annexe', 'depot', 'agence', 'autre'])->default('autre');
            $table->text('adresse')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immobilisation_sites');
    }
};
