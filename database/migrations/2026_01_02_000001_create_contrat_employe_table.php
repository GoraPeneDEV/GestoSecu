<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrat_employe', function (Blueprint $table) {
            $table->id();
            $table->string('type_contrat');
            $table->date('date_debut');
            $table->string('date_prevu_fin')->nullable();
            $table->date('date_fin')->nullable();
            $table->longText('motif')->nullable();
            $table->integer('montant')->nullable();
            $table->string('document')->nullable();
            $table->foreignId('id_employe')->constrained('employe')->cascadeOnDelete();
            $table->tinyInteger('etat')->default(1);
            $table->foreignId('id_user')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrat_employe');
    }
};
