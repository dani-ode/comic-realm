<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tripay_reference', 100)->unique(); // Reference from TriPay
            $table->string('merchant_ref', 100)->index(); // Order number INV-YYYYMMDD-XXXX
            $table->string('payment_method', 50); // BRIVA, BCAVA, QRIS, ALFAMART, etc.
            $table->string('payment_name', 100); // BRI Virtual Account
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('fee_merchant')->default(0);
            $table->unsignedBigInteger('fee_customer')->default(0);
            $table->unsignedBigInteger('total_fee')->default(0);
            $table->unsignedBigInteger('amount_received')->default(0);
            $table->string('pay_code', 255)->nullable();
            $table->string('pay_url', 500)->nullable();
            $table->string('checkout_url', 500)->nullable();
            $table->string('status', 30)->default('UNPAID')->index(); // UNPAID, PAID, EXPIRED, FAILED, REFUND
            $table->json('instructions')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
