<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('designation');
            $table->text('description')->nullable();
            $table->string('unite')->default('Unité');
            $table->integer('stock_minimum')->default(0);
            $table->integer('stock_actuel')->default(0);
            $table->decimal('prix_unitaire', 10, 2)->default(0);
            $table->foreignId('departement_id')->constrained()->restrictOnDelete();
            $table->boolean('est_immobilisable')->default(false);
            $table->foreignId('immobilisation_categorie_id')->nullable()->constrained('immobilisation_categories')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
