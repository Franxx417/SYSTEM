<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->uuid('purchase_order_id')->primary()->default(DB::raw('NEWSEQUENTIALID()'));
                $table->uuid('requestor_id')->nullable();
                $table->uuid('supplier_id')->nullable();
                $table->string('purpose', 255);
                $table->string('purchase_order_no', 50)->unique();
                $table->string('official_receipt_no', 50)->nullable();
                $table->date('date_requested');
                $table->date('delivery_date');
                $table->decimal('shipping_fee', 18, 2)->nullable();
                $table->decimal('discount', 18, 2)->nullable();
                $table->decimal('subtotal', 18, 2)->nullable();
                $table->decimal('total', 18, 2)->nullable();
                $table->dateTime('created_at')->default(DB::raw('GETDATE()'));
                $table->dateTime('updated_at')->default(DB::raw('GETDATE()'));
                $table->foreign('requestor_id')->references('user_id')->on('users');
                $table->foreign('supplier_id')->references('supplier_id')->on('suppliers');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
