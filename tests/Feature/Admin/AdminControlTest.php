<?php

use App\Domain\Publisher\Models\PublisherProfile;
use App\Domain\User\Models\User;

it('allows admin to render admin dashboard overview', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/dashboard');

    $response->assertStatus(200);
});

it('allows admin to approve publisher profile application', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $profile = PublisherProfile::create([
        'user_id' => $user->id,
        'brand_name' => 'Pending Studio',
        'slug' => 'pending-studio',
        'verification_status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->post("/admin/publishers/{$profile->id}/approve");

    $response->assertRedirect();
    expect($profile->fresh()->verification_status->value)->toBe('approved')
        ->and($user->fresh()->role->value)->toBe('publisher');
});
