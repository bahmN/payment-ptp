<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigInteger('invoice_id')->primary();
            $table->float('amount');
            $table->string('currency');
            $table->string('description');
            $table->string('lang');
            $table->string('email');
            $table->string('payment_id');
            $table->string('return_url');
            $table->string('status', 1)->default('N');
            $table->ipAddress('customer_ip');
            $table->timestamp('date');
            $table->string('operation_id', 30);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('order');
    }
};
