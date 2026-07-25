<?php

use App\Domain\Comic\Models\Comic;
use App\Domain\Comic\Models\Genre;
use App\Domain\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('allows regular user to apply for publisher role', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/publisher/apply', [
        'brand_name' => 'Studio Zero',
        'bio' => 'Independent webcomic creator team.',
        'bank_name' => 'BCA',
        'bank_account_number' => '12345678',
        'bank_account_name' => 'Mahdani',
    ]);

    $response->assertRedirect(route('publisher.dashboard'));

    $this->assertDatabaseHas('publisher_profiles', [
        'user_id' => $user->id,
        'brand_name' => 'Studio Zero',
    ]);
});

it('allows publisher to create new comic series and publish chapter with pages', function () {
    Storage::fake('public');

    $genre = Genre::factory()->create();
    $publisher = User::factory()->publisher()->create();

    // 1. Create Comic
    $responseComic = $this->actingAs($publisher)->post('/publisher/comics', [
        'title' => 'The Cyber Ninja',
        'description' => 'A futuristic ninja in 2099.',
        'cover_image' => 'https://picsum.photos/400/600',
        'status' => 'ongoing',
        'genres' => [$genre->id],
    ]);

    $responseComic->assertRedirect(route('publisher.dashboard'));
    $comic = Comic::where('title', 'The Cyber Ninja')->first();

    // 2. Publish Chapter with Fake Images
    $file1 = UploadedFile::fake()->image('001.webp', 800, 1200);
    $file2 = UploadedFile::fake()->image('002.webp', 800, 1200);

    $responseChapter = $this->actingAs($publisher)->post("/publisher/comics/{$comic->id}/chapters", [
        'title' => 'Chapter 1: Cyber City',
        'chapter_number' => 1.0,
        'is_free' => true,
        'price' => 0,
        'pages' => [$file1, $file2],
    ]);

    $responseChapter->assertRedirect(route('publisher.dashboard'));

    $this->assertDatabaseHas('chapters', [
        'comic_id' => $comic->id,
        'chapter_number' => 1.0,
    ]);

    $this->assertDatabaseHas('chapter_pages', [
        'page_number' => 1,
    ]);
});
