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
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};


