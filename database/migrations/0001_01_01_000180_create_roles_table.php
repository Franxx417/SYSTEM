<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->uuid('role_id')->primary()->default(DB::raw('NEWSEQUENTIALID()'));
                $table->uuid('user_id');
                $table->uuid('role_type_id');
                $table->foreign('user_id')->references('user_id')->on('users');
                $table->foreign('role_type_id')->references('role_type_id')->on('role_types');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
