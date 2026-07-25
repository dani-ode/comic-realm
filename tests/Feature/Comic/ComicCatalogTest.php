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
