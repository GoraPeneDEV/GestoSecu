<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('scan_mode');
            $table->decimal('gps_lat', 10, 8)->nullable();
            $table->decimal('gps_lng', 11, 8)->nullable();
            $table->string('status');
            $table->integer('expected_agents_count')->default(0);
            $table->integer('actual_agents_count')->default(0);
            $table->json('missing_agents')->nullable();
            $table->text('missing_agents_details')->nullable();
            $table->boolean('check_agent_presence')->default(false);
            $table->boolean('check_respect_planning')->default(false);
            $table->boolean('check_strict_consignes')->default(false);
            $table->boolean('check_port_vestimentaire')->default(false);
            $table->boolean('check_proprete')->default(false);
            $table->boolean('check_talk_box')->default(false);
            $table->boolean('check_registre_garde')->default(false);
            $table->boolean('ras')->default(false);
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('video_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_visits');
    }
};
