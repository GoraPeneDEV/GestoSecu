<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immobilisation_emplacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('immobilisation_sites')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immobilisation_emplacements');
    }
};
