<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paie_audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();

            $table->string('action', 50);
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id')->nullable();

            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();

            $table->integer('mois')->nullable();
            $table->integer('annee')->nullable();
            $table->foreignId('employe_id')->nullable()->constrained('employe')->nullOnDelete();

            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');

            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['mois', 'annee']);
            $table->index(['employe_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paie_audit_logs');
    }
};
