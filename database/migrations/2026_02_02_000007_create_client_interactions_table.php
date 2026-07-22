<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            $table->enum('type', [
                'appel_entrant',
                'appel_sortant',
                'email_recu',
                'email_envoye',
                'reunion',
                'visite_site',
                'ticket_sav',
                'contrat_signe',
                'facture',
                'relance',
                'autre',
            ]);

            $table->string('sujet');
            $table->text('contenu')->nullable();
            $table->enum('canal', ['telephone', 'email', 'reunion', 'portail', 'courrier', 'autre']);

            $table->enum('sens', ['entrant', 'sortant', 'interne']);

            $table->foreignId('contact_client_id')->nullable()->constrained('client_contacts');
            $table->foreignId('user_id')->constrained();

            $table->nullableMorphs('relatable');

            $table->enum('statut', ['a_traiter', 'en_attente', 'traite', 'urgent'])->default('traite');
            $table->timestamp('rappel_le')->nullable();
            $table->foreignId('rappel_attribue_a')->nullable()->constrained('users');

            $table->json('pieces_jointes')->nullable();

            $table->timestamps();

            $table->index('client_id');
            $table->index(['type', 'created_at']);
            $table->index('rappel_le');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_interactions');
    }
};
