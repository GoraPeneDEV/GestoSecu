<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ajustements_solde_conge', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_employe')->constrained('employe');
            $table->enum('type', ['ajout', 'retrait']);
            $table->unsignedInteger('montant');
            $table->text('commentaire');
            $table->foreignId('id_user')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustements_solde_conge');
    }
};
