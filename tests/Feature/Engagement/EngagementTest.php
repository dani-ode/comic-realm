<?php

use App\Domain\Comic\Models\Comic;
use App\Domain\User\Models\User;

it('allows authenticated users to toggle bookmark on a comic', function () {
    $user = User::factory()->create();
    $comic = Comic::factory()->create(['total_bookmarks' => 0]);

    // Bookmark
    $response = $this->actingAs($user)->post('/api/bookmarks/toggle', [
        'comic_id' => $comic->id,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true, 'bookmarked' => true]);
    $this->assertDatabaseHas('bookmarks', ['user_id' => $user->id, 'comic_id' => $comic->id]);
    expect($comic->fresh()->total_bookmarks)->toBe(1);

    // Unbookmark
    $response2 = $this->actingAs($user)->post('/api/bookmarks/toggle', [
        'comic_id' => $comic->id,
    ]);

    $response2->assertStatus(200);
    $response2->assertJson(['success' => true, 'bookmarked' => false]);
    $this->assertDatabaseMissing('bookmarks', ['user_id' => $user->id, 'comic_id' => $comic->id]);
    expect($comic->fresh()->total_bookmarks)->toBe(0);
});

it('allows authenticated users to rate a comic and updates average', function () {
    $user = User::factory()->create();
    $comic = Comic::factory()->create(['rating_average' => 0.0, 'total_ratings' => 0]);

    $response = $this->actingAs($user)->post('/api/ratings', [
        'comic_id' => $comic->id,
        'rating' => 5,
        'review_text' => 'Amazing comic!',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('ratings', [
        'user_id' => $user->id,
        'comic_id' => $comic->id,
        'rating' => 5,
    ]);

    expect($comic->fresh()->rating_average)->toBe(5.0);
});

it('allows posting and fetching threaded comments', function () {
    $user = User::factory()->create();
    $comic = Comic::factory()->create();

    $response = $this->actingAs($user)->post('/api/comments', [
        'comic_id' => $comic->id,
        'comment_text' => 'This chapter was intense!',
        'is_spoiler' => false,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('comments', [
        'user_id' => $user->id,
        'comic_id' => $comic->id,
        'comment_text' => 'This chapter was intense!',
    ]);

    $fetchResponse = $this->get("/api/comments?comic_id={$comic->id}");
    $fetchResponse->assertStatus(200);
});
