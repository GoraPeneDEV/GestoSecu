<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_ronde_sup', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ronde_sup_id')->constrained('ronde_sup');
            $table->foreignId('point_controle_sup_id')->constrained('points_controle_sup');
            $table->dateTime('date_scan');
            $table->boolean('anomalie')->default(false);
            $table->string('type_anomalie')->nullable();
            $table->string('urgence', 50)->nullable();
            $table->text('commentaire')->nullable();
            $table->json('actions')->nullable();
            $table->string('photo_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_ronde_sup');
    }
};
