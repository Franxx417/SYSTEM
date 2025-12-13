<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('login')) {
            Schema::create('login', function (Blueprint $table) {
                $table->uuid('login_id')->primary()->default(DB::raw('NEWSEQUENTIALID()'));
                $table->uuid('user_id')->unique();
                $table->string('username', 100);
                $table->string('password', 255);
                $table->dateTime('created_at')->default(DB::raw('GETDATE()'));
                $table->dateTime('updated_at')->default(DB::raw('GETDATE()'));
                $table->foreign('user_id')->references('user_id')->on('users');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('login');
    }
};
