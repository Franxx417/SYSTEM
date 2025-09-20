<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->uuid('supplier_id')->primary()->default(DB::raw('NEWSEQUENTIALID()'));
                $table->string('name', 255);
                $table->text('address')->nullable();
                $table->string('vat_type', 50);
                $table->string('contact_person', 100)->nullable();
                $table->string('contact_number', 20)->nullable();
                $table->string('tin_no', 20)->nullable();
                $table->dateTime('created_at')->default(DB::raw('GETDATE()'));
                $table->dateTime('updated_at')->default(DB::raw('GETDATE()'));
                // Note: VAT type validation handled at application level
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};


