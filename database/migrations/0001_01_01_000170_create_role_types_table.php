<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('role_types')) {
            Schema::create('role_types', function (Blueprint $table) {
                $table->uuid('role_type_id')->primary()->default(DB::raw('NEWSEQUENTIALID()'));
                $table->string('user_role_type', 50);
                // Note: Role type validation handled at application level
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_types');
    }
};


