<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employe', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->nullable()->unique();
            $table->string('fonction');
            $table->string('prenom');
            $table->string('nom');
            $table->string('sexe')->default('Homme');
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('telephone')->nullable();
            $table->text('adresse')->nullable();
            $table->string('situation_matrimoniale')->nullable();
            $table->string('niveau_experience')->nullable();
            $table->date('date_debut')->nullable();
            $table->longText('note')->nullable();
            $table->integer('nbr_conges')->default(0);
            $table->string('cni')->nullable();
            $table->text('photo')->nullable();
            $table->text('diplome')->nullable();
            $table->string('niveau_etude')->nullable();
            $table->string('personne_contact')->nullable();
            $table->string('numero_contact')->nullable();
            $table->string('lien_parente')->nullable();
            $table->string('pere')->nullable();
            $table->string('mere')->nullable();
            $table->string('nationnalite', 2)->nullable();
            $table->unsignedBigInteger('id_departement');
            $table->foreign('id_departement')->references('id')->on('departements')->onDelete('restrict');
            $table->date('arret')->nullable();
            $table->text('motif_arret')->nullable();
            $table->text('commentaire')->nullable();
            $table->tinyInteger('etat')->default(1); // 1 = actif, 0 = sorti
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employe');
    }
};
