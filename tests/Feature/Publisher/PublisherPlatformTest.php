<?php

use App\Domain\Comic\Models\Comic;
use App\Domain\Comic\Models\Genre;
use App\Domain\Publisher\Models\PublisherProfile;
use App\Domain\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('redirects user without studio from dashboard to apply page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/publisher/dashboard');

    $response->assertRedirect(route('publisher.apply'));
});

it('allows regular user to apply for publisher role and redirects if already applied', function () {
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

    // Jika mengakses route /publisher/apply lagi saat sudah punya studio -> langsung redirect ke dashboard
    $responseApplyAgain = $this->actingAs($user)->get('/publisher/apply');
    $responseApplyAgain->assertRedirect(route('publisher.dashboard'));
});

it('allows approved publisher to create new comic series and publish chapter with pages', function () {
    Storage::fake('public');

    $genre = Genre::factory()->create();
    $publisher = User::factory()->publisher()->create();

    PublisherProfile::create([
        'user_id' => $publisher->id,
        'brand_name' => 'Studio Ninja',
        'slug' => 'studio-ninja',
        'verification_status' => 'approved',
    ]);

    // 1. Create Comic
    $responseComic = $this->actingAs($publisher)->post('/publisher/comics', [
        'title' => 'The Cyber Ninja',
        'description' => 'A futuristic ninja in 2099.',
        'cover_image' => 'https://picsum.photos/400/600',
        'status' => 'ongoing',
        'genres' => [$genre->id],
    ]);

    $responseComic->assertRedirect(route('publisher.comics.index'));
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

it('blocks unapproved studio from creating new comics', function () {
    $genre = Genre::factory()->create();
    $user = User::factory()->create();

    // Studio pending approval
    PublisherProfile::create([
        'user_id' => $user->id,
        'brand_name' => 'Pending Studio',
        'slug' => 'pending-studio',
        'verification_status' => 'pending',
    ]);

    $response = $this->actingAs($user)->post('/publisher/comics', [
        'title' => 'Unapproved Comic',
        'description' => 'Test',
        'cover_image' => 'https://picsum.photos/400/600',
        'status' => 'ongoing',
        'genres' => [$genre->id],
    ]);

    $response->assertRedirect(route('publisher.dashboard'));
    $this->assertDatabaseMissing('comics', ['title' => 'Unapproved Comic']);
});

it('allows rejected publisher to edit and resubmit studio profile details', function () {
    $user = User::factory()->create();

    $profile = PublisherProfile::create([
        'user_id' => $user->id,
        'brand_name' => 'Rejected Studio',
        'slug' => 'rejected-studio',
        'verification_status' => 'rejected',
        'rejection_reason' => 'Nama studio kurang jelas.',
    ]);

    $response = $this->actingAs($user)->post('/publisher/profile/update', [
        'brand_name' => 'Updated Studio Realm',
        'bio' => 'Bio baru yang sudah diperbaiki.',
        'bank_name' => 'BCA',
        'bank_account_number' => '999888777',
        'bank_account_name' => 'Mahdani Studio',
    ]);

    $response->assertRedirect(route('publisher.profile.edit'));

    expect($profile->fresh())
        ->brand_name->toBe('Updated Studio Realm')
        ->verification_status->value->toBe('pending')
        ->rejection_reason->toBeNull();
});
