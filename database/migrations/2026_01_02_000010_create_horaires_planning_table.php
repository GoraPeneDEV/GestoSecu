<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horaires_planning', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->decimal('nombre_heures', 4, 2)
                ->storedAs("CASE
                              WHEN heure_debut = heure_fin THEN 0
                              WHEN heure_fin > heure_debut
                                THEN TIMESTAMPDIFF(HOUR, heure_debut, heure_fin)
                              ELSE TIMESTAMPDIFF(HOUR, heure_debut, heure_fin) + 24
                           END");
            $table->timestamps();
        });

        DB::table('horaires_planning')->insert([
            ['label' => 'Jour', 'heure_debut' => '07:00', 'heure_fin' => '19:00'],
            ['label' => 'Nuit', 'heure_debut' => '19:00', 'heure_fin' => '07:00'],
            ['label' => 'Matin', 'heure_debut' => '07:00', 'heure_fin' => '15:00'],
            ['label' => 'Soir', 'heure_debut' => '15:00', 'heure_fin' => '23:00'],
            ['label' => 'Nuit 2', 'heure_debut' => '23:00', 'heure_fin' => '07:00'],
            ['label' => 'Repos', 'heure_debut' => '00:00', 'heure_fin' => '00:00'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('horaires_planning');
    }
};
