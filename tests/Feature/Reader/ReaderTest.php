<?php

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use App\Domain\User\Models\User;

it('allows reading free chapter for guest users', function () {
    $comic = Comic::factory()->create(['publication_status' => 'published']);
    $chapter = Chapter::factory()->create([
        'comic_id' => $comic->id,
        'chapter_number' => 1,
        'is_free' => true,
        'status' => 'published',
    ]);

    $response = $this->get("/read/{$comic->slug}/1");

    $response->assertStatus(200);
});

it('redirects guest users when trying to access paid chapter', function () {
    $comic = Comic::factory()->create(['publication_status' => 'published']);
    $chapter = Chapter::factory()->paid(5000)->create([
        'comic_id' => $comic->id,
        'chapter_number' => 3,
        'status' => 'published',
    ]);

    $response = $this->get("/read/{$comic->slug}/3");

    $response->assertRedirect(route('comics.show', $comic->slug));
});

it('allows authenticated users to save reading progress', function () {
    $user = User::factory()->create();
    $comic = Comic::factory()->create();
    $chapter = Chapter::factory()->create(['comic_id' => $comic->id]);

    $response = $this->actingAs($user)->post('/api/reader/progress', [
        'comic_id' => $comic->id,
        'chapter_id' => $chapter->id,
        'page_number' => 5,
        'progress_percent' => 50.0,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('reading_progress', [
        'user_id' => $user->id,
        'chapter_id' => $chapter->id,
        'page_number' => 5,
    ]);
});
