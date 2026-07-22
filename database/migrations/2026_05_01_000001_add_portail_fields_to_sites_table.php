<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('numero_rpe')->nullable();
            $table->string('region')->nullable();
            $table->text('risques')->nullable();
            $table->enum('type_site', ['gardiennage', 'nettoyage', 'mixte'])->default('mixte');
            $table->date('date_arret')->nullable();
            $table->text('motif_arret')->nullable();
            $table->foreignId('supprime_par')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropForeign(['supprime_par']);
            $table->dropColumn(['numero_rpe', 'region', 'risques', 'type_site', 'date_arret', 'motif_arret', 'supprime_par']);
        });
    }
};
