<?php

use App\Domain\Comic\Models\Comic;

it('renders home page with featured and popular comics', function () {
    Comic::factory()->featured()->create();
    Comic::factory()->count(3)->create();

    $response = $this->get('/');

    $response->assertStatus(200);
});

it('renders catalog page with paginated comics', function () {
    Comic::factory()->count(5)->create();

    $response = $this->get('/comics');

    $response->assertStatus(200);
});

it('renders comic detail page by slug', function () {
    $comic = Comic::factory()->create(['publication_status' => 'published']);

    $response = $this->get("/comics/{$comic->slug}");

    $response->assertStatus(200);
});

it('renders public studio profile page', function () {
    $publisher = \App\Domain\User\Models\User::factory()->create(['role' => 'publisher']);
    \App\Domain\Publisher\Models\PublisherProfile::create([
        'user_id' => $publisher->id,
        'brand_name' => 'Test Studio',
        'slug' => 'test-studio',
    ]);
    Comic::factory()->create(['publisher_id' => $publisher->id, 'publication_status' => 'published']);

    $response = $this->get("/studios/{$publisher->id}");

    $response->assertStatus(200);
});

it('renders studio list page with card grid', function () {
    $response = $this->get('/studios');

    $response->assertStatus(200);
});
