<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('NEWSEQUENTIALID()'));
                $table->string('category', 50); // user_management, security, notifications, performance, etc.
                $table->string('key', 100);
                $table->text('value')->nullable();
                $table->string('type', 20)->default('string'); // string, integer, boolean, json, encrypted
                $table->text('description')->nullable();
                $table->json('validation_rules')->nullable(); // Store validation rules as JSON
                $table->boolean('is_encrypted')->default(false);
                $table->boolean('is_public')->default(false); // Can be accessed by non-admin users
                $table->integer('sort_order')->default(0);
                $table->uuid('updated_by')->nullable();
                $table->dateTime('created_at')->default(DB::raw('GETDATE()'));
                $table->dateTime('updated_at')->default(DB::raw('GETDATE()'));

                // Indexes
                $table->unique(['category', 'key']);
                $table->index('category');
                $table->index('is_public');
                $table->index('sort_order');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
