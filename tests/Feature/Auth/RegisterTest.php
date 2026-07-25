<?php

use App\Domain\User\Models\User;

it('renders register page successfully', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

it('registers a new user and creates profile automatically', function () {
    $response = $this->post('/register', [
        'name' => 'La Ode Mahdani',
        'username' => 'mahdani',
        'email' => 'mahdani@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect(route('home'));

    $this->assertDatabaseHas('users', [
        'username' => 'mahdani',
        'email' => 'mahdani@example.com',
    ]);

    $user = User::where('username', 'mahdani')->first();
    expect($user)->not->toBeNull()
        ->and($user->profile)->not->toBeNull();

    $this->assertAuthenticatedAs($user);
});
