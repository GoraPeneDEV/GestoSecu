<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employe', function (Blueprint $table) {
            $table->integer('nbr_femme')->default(0)->after('situation_matrimoniale');
            $table->integer('nbr_enfants')->default(0)->after('nbr_femme');
            $table->string('arts_martiaux')->default('Non')->after('cni');
            $table->date('date_delivrance')->nullable()->after('arts_martiaux');
            $table->foreignId('id_user')->nullable()->after('id_departement')->constrained('users')->nullOnDelete();
            $table->string('permis')->default('Non')->after('niveau_etude');
            $table->string('banque')->nullable()->after('permis');
            $table->string('compte_bancaire')->nullable()->after('banque');
            $table->string('langues_parlees')->nullable()->after('compte_bancaire');
            $table->string('langues_lues')->nullable()->after('langues_parlees');
            $table->string('service_militaire')->default('Non')->after('langues_lues');
            $table->string('corps_militaire')->nullable()->after('service_militaire');
            $table->date('date_debut_service')->nullable()->after('corps_militaire');
            $table->date('date_fin_service')->nullable()->after('date_debut_service');
        });
    }

    public function down(): void
    {
        Schema::table('employe', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_user');
            $table->dropColumn([
                'nbr_femme', 'nbr_enfants', 'arts_martiaux', 'date_delivrance',
                'permis', 'banque', 'compte_bancaire', 'langues_parlees', 'langues_lues',
                'service_militaire', 'corps_militaire', 'date_debut_service', 'date_fin_service',
            ]);
        });
    }
};
