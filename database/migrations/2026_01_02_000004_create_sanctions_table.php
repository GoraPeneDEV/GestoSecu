<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sanctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employe')->cascadeOnDelete();
            $table->integer('mois');
            $table->integer('annee');
            $table->string('motif');
            $table->decimal('montant', 10, 2)->default(0);
            $table->date('date');
            $table->text('observation')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanctions');
    }
};
