<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_ronde_sup_sites', function (Blueprint $table) {
            $table->foreignId('planning_ronde_sup_id')->constrained('planning_ronde_sup');
            $table->foreignId('site_id')->constrained('sites');
            $table->integer('ordre');
            $table->timestamps();
            $table->primary(['planning_ronde_sup_id', 'site_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_ronde_sup_sites');
    }
};
