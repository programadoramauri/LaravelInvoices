<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Cria os roles usados em todos os testes que precisam
    Role::findOrCreate('admin');
    Role::findOrCreate('editor');
});

it('redirects guest users trying to access protected route', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('allows authenticated users to access protected route', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Logged user dashboard');
});

it('redirects authenticated users away from guest-only pages', function (string $uri) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get($uri)
        ->assertRedirect('/dashboard');
})->with([
    '/login',
    '/register',
    '/forgot-password',
    'reset-password/any-token',
    'two-factor-challenge',
]);

it('forbids access to admin-only route if user is not admin', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('allows access to admin-only route if user has admin role', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Admin area');
});

it('allows access to panel for admin or editor', function () {
    $user = User::factory()->create();
    $user->assignRole('editor');

    $this->actingAs($user)
        ->get('/panel')
        ->assertOk()
        ->assertSee('Admin or editor panel');
});
