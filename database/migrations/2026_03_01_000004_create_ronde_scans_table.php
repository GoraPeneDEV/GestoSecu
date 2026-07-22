<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ronde_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ronde_id')->constrained('rondes')->onDelete('cascade');
            $table->foreignId('point_controle_id')->constrained('point_controles');
            $table->dateTime('date_scan');
            $table->boolean('anomalie')->default(false);
            $table->string('type_anomalie')->nullable();
            $table->enum('urgence', ['faible', 'moyen', 'eleve'])->nullable();
            $table->text('commentaire')->nullable();
            $table->json('photos')->nullable();
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->json('actions')->nullable();
            $table->string('photo_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ronde_scans');
    }
};
