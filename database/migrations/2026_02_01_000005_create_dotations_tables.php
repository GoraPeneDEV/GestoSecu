<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dotations', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->timestamp('date_dotation');
            $table->enum('type_dotation', ['INITIALE', 'RENOUVELLEMENT']);
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->foreignId('employe_id')->nullable()->constrained('employe')->cascadeOnDelete();
            $table->string('motif')->nullable();
            $table->string('document_path')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dotation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->constrained()->restrictOnDelete();
            $table->integer('quantite');
            $table->boolean('is_returned')->default(false);
            $table->date('date_retour')->nullable();
            $table->string('statut_retour')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['dotation_id', 'article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dotation_details');
        Schema::dropIfExists('dotations');
    }
};
