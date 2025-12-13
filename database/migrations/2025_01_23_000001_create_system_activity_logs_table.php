<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_activity_logs')) {
            Schema::create('system_activity_logs', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('NEWSEQUENTIALID()'));
                $table->uuid('user_id')->nullable();
                $table->string('username', 100)->nullable();
                $table->string('action', 100); // login, logout, create, update, delete, view, etc.
                $table->string('resource_type', 50)->nullable(); // users, purchase_orders, suppliers, etc.
                $table->string('resource_id', 100)->nullable();
                $table->text('description')->nullable();
                $table->json('metadata')->nullable(); // Additional context data
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->string('session_id', 100)->nullable();
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
                $table->boolean('is_security_event')->default(false);
                $table->dateTime('created_at')->default(DB::raw('GETDATE()'));

                // Indexes for performance
                $table->index(['user_id', 'created_at']);
                $table->index(['action', 'created_at']);
                $table->index(['resource_type', 'resource_id']);
                $table->index(['is_security_event', 'created_at']);
                $table->index(['severity', 'created_at']);
                $table->index('ip_address');
                $table->index('session_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_activity_logs');
    }
};
