<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->uuid('user_id')->primary()->default(DB::raw('NEWSEQUENTIALID()'));
                $table->string('name', 200)->nullable();
                $table->string('email', 255)->unique();
                $table->string('position', 100);
                $table->string('department', 100);
                $table->dateTime('created_at')->default(DB::raw('GETDATE()'));
                $table->dateTime('updated_at')->default(DB::raw('GETDATE()'));
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};


