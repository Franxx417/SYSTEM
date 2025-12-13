<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('security_sessions')) {
            Schema::create('security_sessions', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->uuid('user_id')->nullable();
                $table->string('username', 100)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->text('payload')->nullable();
                $table->dateTime('last_activity')->default(DB::raw('GETDATE()'));
                $table->dateTime('login_at')->default(DB::raw('GETDATE()'));
                $table->boolean('is_active')->default(true);
                $table->string('device_fingerprint', 255)->nullable();
                $table->json('location_data')->nullable(); // Country, city, etc.
                $table->dateTime('expires_at')->nullable();

                // Indexes
                $table->index(['user_id', 'is_active']);
                $table->index(['ip_address', 'last_activity']);
                $table->index(['last_activity', 'is_active']);
                $table->index('expires_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('security_sessions');
    }
};
