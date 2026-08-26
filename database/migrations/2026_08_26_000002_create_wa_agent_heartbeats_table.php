<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_agent_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->string('agent_name', 100)->default('default');
            $table->string('status', 20)->default('online');
            $table->boolean('whatsapp_ready')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('agent_name');
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_agent_heartbeats');
    }
};
