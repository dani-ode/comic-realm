<?php

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use App\Domain\User\Models\User;

it('creates order from cart and clears cart items', function () {
    $user = User::factory()->create();
    $comic = Comic::factory()->create();
    $paidChapter = Chapter::factory()->paid(5000)->create(['comic_id' => $comic->id]);

    $cart = Cart::create(['user_id' => $user->id, 'total_amount' => 5000]);
    CartItem::create(['cart_id' => $cart->id, 'chapter_id' => $paidChapter->id, 'price' => 5000]);

    $response = $this->actingAs($user)->post('/api/checkout/process');

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'subtotal' => 5000,
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('order_items', [
        'comic_id' => $comic->id,
        'chapter_id' => $paidChapter->id,
        'price' => 5000,
    ]);

    // Cart cleared
    expect($cart->fresh()->items->count())->toBe(0)
        ->and($cart->fresh()->total_amount)->toBe(0);
});

it('prevents checkout with empty cart', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/api/checkout/process');

    $response->assertStatus(422);
});
