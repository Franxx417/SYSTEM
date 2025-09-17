<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('approvals')) {
            Schema::create('approvals', function (Blueprint $table) {
                $table->uuid('approval_id')->primary()->default(DB::raw('NEWSEQUENTIALID()'));
                $table->uuid('purchase_order_id')->nullable();
                $table->uuid('prepared_by_id')->nullable();
                $table->dateTime('prepared_at');
                $table->uuid('verified_by_id')->nullable();
                $table->dateTime('verified_at')->nullable();
                $table->uuid('approved_by_id')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->uuid('received_by_id')->nullable();
                $table->dateTime('received_at')->nullable();
                $table->uuid('status_id');
                $table->text('remarks')->nullable();
                $table->foreign('purchase_order_id')->references('purchase_order_id')->on('purchase_orders');
                $table->foreign('prepared_by_id')->references('user_id')->on('users');
                $table->foreign('verified_by_id')->references('user_id')->on('users');
                $table->foreign('approved_by_id')->references('user_id')->on('users');
                $table->foreign('received_by_id')->references('user_id')->on('users');
                $table->foreign('status_id')->references('status_id')->on('statuses');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};


