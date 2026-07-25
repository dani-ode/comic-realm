<?php

use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Domain\Publisher\Models\PublisherProfile;
use App\Domain\User\Models\User;
use App\Domain\Wallet\Actions\CreditPublisherRoyalty;

it('credits publisher royalty share 70% from completed order', function () {
    $publisherUser = User::factory()->publisher()->create();
    $publisherProfile = PublisherProfile::create([
        'user_id' => $publisherUser->id,
        'brand_name' => 'Royalty Studio',
        'slug' => 'royalty-studio',
    ]);

    $customer = User::factory()->create();
    $order = Order::create([
        'user_id' => $customer->id,
        'order_number' => 'INV-ROYALTY-001',
        'subtotal' => 10000,
        'tax_amount' => 0,
        'fee_amount' => 0,
        'total_amount' => 10000,
        'status' => 'completed',
    ]);

    $comic = \App\Domain\Comic\Models\Comic::factory()->create(['publisher_id' => $publisherUser->id]);
    $chapter = \App\Domain\Comic\Models\Chapter::factory()->create(['comic_id' => $comic->id]);

    OrderItem::create([
        'order_id' => $order->id,
        'comic_id' => $comic->id,
        'chapter_id' => $chapter->id,
        'title_snapshot' => $comic->title . ' - Ch 1',
        'chapter_number_snapshot' => 1.0,
        'price' => 10000,
    ]);

    $action = app(CreditPublisherRoyalty::class);
    $action->execute($order, 0.70);

    $this->assertDatabaseHas('publisher_wallets', [
        'publisher_id' => $publisherProfile->id,
        'balance' => 7000,
        'total_earned' => 7000,
    ]);

    $this->assertDatabaseHas('wallet_transactions', [
        'amount' => 7000,
        'type' => 'credit',
    ]);
});

it('allows publisher to request payout withdrawal', function () {
    $publisherUser = User::factory()->publisher()->create();
    $publisherProfile = PublisherProfile::create([
        'user_id' => $publisherUser->id,
        'brand_name' => 'Withdrawal Studio',
        'slug' => 'withdrawal-studio',
    ]);

    $wallet = \App\Domain\Wallet\Models\PublisherWallet::create([
        'publisher_id' => $publisherProfile->id,
        'balance' => 100000,
        'total_earned' => 100000,
        'total_withdrawn' => 0,
    ]);

    $response = $this->actingAs($publisherUser)->post('/publisher/wallet/withdraw', [
        'amount' => 50000,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('publisher_wallets', [
        'id' => $wallet->id,
        'balance' => 50000,
        'total_withdrawn' => 50000,
    ]);

    $this->assertDatabaseHas('withdrawal_requests', [
        'publisher_id' => $publisherProfile->id,
        'amount' => 50000,
        'status' => 'pending',
    ]);
});
