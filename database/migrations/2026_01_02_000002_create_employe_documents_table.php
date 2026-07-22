<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employe_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employe')->cascadeOnDelete();
            $table->string('type_document');
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->foreignId('ajoute_par')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employe_documents');
    }
};
