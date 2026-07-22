<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paie_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('titre');
            $table->text('message');
            $table->string('niveau')->default('info');

            $table->integer('periode_mois')->nullable();
            $table->integer('periode_annee')->nullable();

            $table->json('data')->nullable();

            $table->boolean('est_lue')->default(false);
            $table->foreignId('lue_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_lecture')->nullable();

            $table->timestamp('expire_le')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'est_lue']);
            $table->index(['periode_mois', 'periode_annee']);
            $table->index('expire_le');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paie_alerts');
    }
};
