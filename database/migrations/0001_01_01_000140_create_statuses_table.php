<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('statuses')) {
            Schema::create('statuses', function (Blueprint $table) {
                $table->uuid('status_id')->primary()->default(DB::raw('NEWSEQUENTIALID()'));
                $table->string('status_name', 50)->unique();
                $table->text('description')->nullable();
                $table->string('color', 7)->nullable()->default('#6c757d');
                $table->integer('sort_order')->nullable()->default(0);
                $table->timestamps();
            });
        } else {
            // Add color column if it doesn't exist
            Schema::table('statuses', function (Blueprint $table) {
                if (!Schema::hasColumn('statuses', 'color')) {
                    $table->string('color', 7)->nullable()->default('#6c757d');
                }
                if (!Schema::hasColumn('statuses', 'sort_order')) {
                    $table->integer('sort_order')->nullable()->default(0);
                }
                if (!Schema::hasColumn('statuses', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};


