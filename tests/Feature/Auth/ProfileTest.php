<?php

use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

it('allows authenticated user to update their profile info', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'username' => 'originaluser',
        'email' => 'original@test.com',
    ]);

    $response = $this->actingAs($user)->post('/profile/update', [
        'name' => 'Updated Name',
        'username' => 'updateduser',
        'email' => 'updated@test.com',
        'phone' => '081299998888',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'username' => 'updateduser',
        'email' => 'updated@test.com',
        'phone' => '081299998888',
    ]);
});

it('allows authenticated user to update their password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->actingAs($user)->post('/profile/password', [
        'current_password' => 'password123',
        'password' => 'newsecretpassword',
        'password_confirmation' => 'newsecretpassword',
    ]);

    $response->assertRedirect();

    expect(Hash::check('newsecretpassword', $user->fresh()->password))->toBeTrue();
});
