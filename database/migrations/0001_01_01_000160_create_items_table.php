<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->uuid('item_id')->primary()->default(DB::raw('NEWSEQUENTIALID()'));
                $table->uuid('purchase_order_id')->nullable();
                $table->text('item_description');
                $table->integer('quantity');
                $table->decimal('unit_price', 18, 2);
                $table->decimal('total_cost', 18, 2);
                $table->dateTime('created_at')->default(DB::raw('GETDATE()'));
                $table->dateTime('updated_at')->default(DB::raw('GETDATE()'));
                $table->foreign('purchase_order_id')->references('purchase_order_id')->on('purchase_orders');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};


