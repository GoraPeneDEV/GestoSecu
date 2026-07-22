<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('departement_id')->nullable()->after('status')->constrained('departements')->nullOnDelete();
            $table->foreignId('id_employe')->nullable()->after('departement_id')->constrained('employe')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('departement_id');
            $table->dropConstrainedForeignId('id_employe');
        });
    }
};
