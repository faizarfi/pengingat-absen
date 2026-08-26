<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_logs', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('type');
            $table->string('status')->default('pending')->after('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::table('wa_logs', function (Blueprint $table) {
            $table->dropColumn(['scheduled_at', 'status']);
        });
    }
};
