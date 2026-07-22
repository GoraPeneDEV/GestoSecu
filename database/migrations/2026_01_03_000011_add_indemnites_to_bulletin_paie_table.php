<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulletin_paie', function (Blueprint $table) {
            $table->decimal('indemnite_transport', 12, 2)->default(0)->after('total_autres_retenues');
            $table->decimal('prime_panier', 12, 2)->default(0)->after('indemnite_transport');
        });
    }

    public function down(): void
    {
        Schema::table('bulletin_paie', function (Blueprint $table) {
            $table->dropColumn(['indemnite_transport', 'prime_panier']);
        });
    }
};
