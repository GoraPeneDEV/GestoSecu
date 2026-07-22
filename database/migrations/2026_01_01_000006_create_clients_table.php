<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nomClient');
            $table->string('numeroClient')->unique();
            $table->string('emailClient')->nullable();
            $table->string('telephoneClient')->nullable();
            $table->string('addresseClient')->nullable();
            $table->string('nomContactClient')->nullable();
            $table->string('infoContactClient')->nullable();
            $table->enum('typeClient', [
                'Banque',
                'Entreprise',
                'Societe',
                'Ong',
                'Gouvernement',
                'Hotel',
                'Particulier',
                'Association',
                'Organisme',
                'Non Enregistrer',
            ])->default('Non Enregistrer');

            // Classification SAV
            $table->enum('type_client', ['prospect', 'client_actif', 'client_inactif', 'ancien_client'])
                ->default('prospect');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'vip'])->default('normale');

            // Contact principal
            $table->string('contact_principal_nom')->nullable();
            $table->string('contact_principal_fonction')->nullable();
            $table->string('contact_principal_tel')->nullable();
            $table->string('contact_principal_email')->nullable();

            // Informations SAV
            $table->text('notes_internes')->nullable();
            $table->json('preferences_contact')->nullable();
            $table->boolean('alerte_sav_active')->default(false);

            // Responsables
            $table->foreignId('responsable_commercial_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('responsable_sav_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('type_client');
            $table->index('priorite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
