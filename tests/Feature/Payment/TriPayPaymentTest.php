<?php

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Domain\User\Models\User;

it('retrieves available payment channels', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/api/payment/channels');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
});

it('creates payment transaction for pending order', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'INV-TEST-0001',
        'subtotal' => 5000,
        'tax_amount' => 0,
        'fee_amount' => 0,
        'total_amount' => 5000,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)->post('/api/payment/process', [
        'order_number' => $order->order_number,
        'payment_method' => 'BRIVA',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'payment_method' => 'BRIVA',
        'status' => 'UNPAID',
    ]);
});

it('processes webhook callback and grants entitlement', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'INV-WEBHOOK-001',
        'subtotal' => 5000,
        'tax_amount' => 0,
        'fee_amount' => 0,
        'total_amount' => 5000,
        'status' => 'pending',
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'tripay_reference' => 'T0001000999',
        'merchant_ref' => 'INV-WEBHOOK-001',
        'payment_method' => 'BRIVA',
        'payment_name' => 'BRI Virtual Account',
        'amount' => 5000,
        'status' => 'UNPAID',
    ]);

    // Simulate Webhook payload signature
    $privateKey = config('services.tripay.private_key') ?: 'sandbox-privatekey';
    $payload = [
        'reference' => 'T0001000999',
        'merchant_ref' => 'INV-WEBHOOK-001',
        'status' => 'PAID',
        'total_amount' => 5000,
        'is_closed_payment' => 1,
    ];
    $json = json_encode($payload);
    $signature = hash_hmac('sha256', $json, $privateKey);

    $response = $this->call('POST', '/api/payment/tripay/webhook', [], [], [], [
        'HTTP_X_CALLBACK_SIGNATURE' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $json);

    $response->assertStatus(200);
    expect($payment->fresh()->status->value)->toBe('PAID')
        ->and($order->fresh()->status->value)->toBe('completed');
});

it('allows user to check transaction status via check-status endpoint', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'INV-CHECK-001',
        'subtotal' => 10000,
        'tax_amount' => 0,
        'fee_amount' => 0,
        'total_amount' => 10000,
        'status' => 'pending',
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'tripay_reference' => 'DEV-T23062389444G3FIC',
        'merchant_ref' => 'INV-CHECK-001',
        'payment_method' => 'QRIS2',
        'payment_name' => 'QRIS',
        'amount' => 10000,
        'status' => 'UNPAID',
    ]);

    $response = $this->actingAs($user)->post('/api/payment/check-status', [
        'reference' => 'DEV-T23062389444G3FIC',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
});
