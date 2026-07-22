<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes_explications', function (Blueprint $table) {
            $table->string('reponse_document_path')->nullable()->after('reponse_employe');
        });
    }

    public function down(): void
    {
        Schema::table('demandes_explications', function (Blueprint $table) {
            $table->dropColumn('reponse_document_path');
        });
    }
};
