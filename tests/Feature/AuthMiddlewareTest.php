<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('middleware', 'routes');

function authOnlyRoutes(): array
{
    return [
        '/dashboard',
    ];
}

function guestOnlyRoutes(): array
{
    return [
        '/login',
        '/register',
        '/forgot-password',
        '/reset-password/any-token',
        '/two-factor-challenge',
    ];
}

it('redirects guests from auth-only pages to login', function (string $uri) {
    $this->get($uri)
        ->assertRedirect('/login');
})->with(authOnlyRoutes());

it('lets authenticated users access auth-only pages', function (string $uri) {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get($uri)
        ->assertStatus(200);
})->with(authOnlyRoutes());

it('redirects authenticated users away from guest-only pages', function (string $uri) {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get($uri)
        ->assertRedirect('/dashboard');
})->with(guestOnlyRoutes());

it('redirects guests POSTing to logout to login', function () {
    $this->post('/logout')
        ->assertRedirect('/login');
});

it('allows authenticated users to logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    // Fortify’s default is redirect to '/'
    $response->assertRedirect('/');

    $this->assertGuest();
});
