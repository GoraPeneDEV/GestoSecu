<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes_absences_administration', function (Blueprint $table) {
            $table->foreignId('cree_par')->nullable()->after('id_employe')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('demandes_absences_administration', function (Blueprint $table) {
            $table->dropForeign(['cree_par']);
            $table->dropColumn('cree_par');
        });
    }
};
