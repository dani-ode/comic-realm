<?php

use App\Domain\User\Models\User;

it('authenticates user with email and password', function () {
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'login' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user);
});

it('authenticates user with username and password', function () {
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'login' => 'testuser',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user);
});

it('fails authentication with invalid credentials and returns validation error', function () {
    $user = User::factory()->create([
        'username' => 'validuser',
        'email' => 'valid@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'login' => 'valid@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors('login');
    $this->assertGuest();
});
