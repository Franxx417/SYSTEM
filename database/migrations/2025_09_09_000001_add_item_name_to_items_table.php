<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('items','item_name')) {
            Schema::table('items', function (Blueprint $table) {
                $table->string('item_name', 255)->nullable()->after('purchase_order_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('items','item_name')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('item_name');
            });
        }
    }
};


