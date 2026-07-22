<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_explications', function (Blueprint $table) {
            $table->id();
            $table->string('numero_demande')->unique();
            $table->foreignId('employe_id')->constrained('employe')->cascadeOnDelete();
            $table->string('motif');
            $table->text('description');
            $table->date('date_incident');
            $table->enum('statut', ['en_attente', 'repondue'])->default('en_attente');
            $table->string('document_path')->nullable();
            $table->text('reponse_employe')->nullable();
            $table->date('date_reponse')->nullable();
            $table->foreignId('cree_par')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_explications');
    }
};
