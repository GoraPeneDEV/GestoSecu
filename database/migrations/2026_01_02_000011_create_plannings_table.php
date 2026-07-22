<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plannings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employe');
            $table->foreignId('site_id')->constrained('sites');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->enum('type_planning', ['horizontal', 'vertical']);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employe_id', 'date_debut', 'date_fin']);
            $table->index('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plannings');
    }
};
