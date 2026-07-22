<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employe', function (Blueprint $table) {
            $table->decimal('solde_conges', 5, 2)->default(0)->after('etat');
        });
    }

    public function down(): void
    {
        Schema::table('employe', function (Blueprint $table) {
            $table->dropColumn('solde_conges');
        });
    }
};
