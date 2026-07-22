<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_absences_administration', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_employe')->constrained('employe');
            $table->string('type_conges');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('nbr_jour');
            $table->text('motif');
            $table->string('statut')->default('en_attente');
            $table->foreignId('id_superieur')->nullable()->constrained('users');
            $table->foreignId('id_rh')->nullable()->constrained('users');
            $table->text('commentaire_sup')->nullable();
            $table->text('commentaire_rh')->nullable();
            $table->text('motif_annulation_rh')->nullable();
            $table->foreignId('id_rh_annulation')->nullable()->constrained('users');
            $table->dateTime('date_annulation')->nullable();
            $table->boolean('a_deduire')->default(false);
            $table->dateTime('date_validation_sup')->nullable();
            $table->dateTime('date_val_rh')->nullable();
            $table->dateTime('date_enregistrement');
            $table->string('document_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_absences_administration');
    }
};
