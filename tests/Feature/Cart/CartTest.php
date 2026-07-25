<?php

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use App\Domain\User\Models\User;

it('prevents adding free chapter to cart', function () {
    $user = User::factory()->create();
    $comic = Comic::factory()->create();
    $freeChapter = Chapter::factory()->create(['comic_id' => $comic->id, 'is_free' => true]);

    $response = $this->actingAs($user)->post('/api/cart/items', [
        'chapter_id' => $freeChapter->id,
    ]);

    $response->assertStatus(422);
});

it('allows adding paid chapter to cart and recalculates total', function () {
    $user = User::factory()->create();
    $comic = Comic::factory()->create();
    $paidChapter = Chapter::factory()->paid(5000)->create(['comic_id' => $comic->id]);

    $response = $this->actingAs($user)->post('/api/cart/items', [
        'chapter_id' => $paidChapter->id,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'total_amount' => 5000,
    ]);
    $this->assertDatabaseHas('cart_items', [
        'chapter_id' => $paidChapter->id,
        'price' => 5000,
    ]);
});

it('allows removing item from cart and clearing cart', function () {
    $user = User::factory()->create();
    $comic = Comic::factory()->create();
    $paidChapter = Chapter::factory()->paid(5000)->create(['comic_id' => $comic->id]);

    // Add item first
    $this->actingAs($user)->post('/api/cart/items', ['chapter_id' => $paidChapter->id]);

    // Remove item
    $removeResponse = $this->actingAs($user)->delete("/api/cart/items/{$paidChapter->id}");
    $removeResponse->assertStatus(200);

    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'total_amount' => 0,
    ]);

    // Clear cart
    $clearResponse = $this->actingAs($user)->delete('/api/cart/clear');
    $clearResponse->assertStatus(200);
});
