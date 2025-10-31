<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('security_alerts')) {
            Schema::create('security_alerts', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('NEWSEQUENTIALID()'));
                $table->string('alert_type', 50); // failed_login, suspicious_activity, privilege_escalation, etc.
                $table->enum('severity', ['low', 'medium', 'high', 'critical']);
                $table->string('title', 200);
                $table->text('description');
                $table->uuid('user_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->json('metadata')->nullable(); // Additional alert data
                $table->boolean('is_resolved')->default(false);
                $table->uuid('resolved_by')->nullable();
                $table->dateTime('resolved_at')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->dateTime('created_at')->default(DB::raw('GETDATE()'));
                
                // Indexes
                $table->index(['alert_type', 'created_at']);
                $table->index(['severity', 'is_resolved']);
                $table->index(['user_id', 'created_at']);
                $table->index(['ip_address', 'created_at']);
                $table->index('is_resolved');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
    }
};
