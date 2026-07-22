<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiche_progres_pieces_jointes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiche_progres_id')->constrained('fiches_progres')->cascadeOnDelete();
            $table->string('filename');
            $table->string('chemin_fichier');
            $table->enum('type', ['photo', 'document', 'capture_ecran', 'autre']);
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();

            $table->index('fiche_progres_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiche_progres_pieces_jointes');
    }
};
