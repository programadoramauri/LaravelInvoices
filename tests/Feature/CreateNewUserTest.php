<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    Artisan::call('migrate');
});

it('creates a new user with valid data', function () {
    $action = new CreateNewUser;
    $password = fake()->password(8);
    $user = $action->create([
        'name' => fake()->name(),
        'email' => fake()->safeEmail(),
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    expect($user)->toBeInstanceOf(User::class);
});

it('fails if password confirmation does not match', function () {
    $password = fake()->password(8);
    expect(fn () => (new CreateNewUser)->create([
        'name' => fake()->name(),
        'email' => fake()->safeEmail(),
        'password' => $password,
        'password_confirmation' => $password.'diff',
    ]))->toThrow(ValidationException::class);
});

it('fails if email is already taken', function () {
    $email = fake()->safeEmail();

    User::factory()->create(['email' => $email]);

    expect(fn () => (new CreateNewUser)->create([
        'name' => fake()->name(),
        'email' => $email,
        'password' => 'pass1234',
        'password_confirmation' => 'pass1234',
    ]))->toThrow(ValidationException::class);
});

it('fails with invalid email format', function () {
    expect(fn () => (new CreateNewUser)->create([
        'name' => fake()->name(),
        'email' => 'invalid-email',
        'password' => 'pass1234',
        'password_confirmation' => 'pass1234',
    ]))->toThrow(ValidationException::class);
});

it('fails with too short password', function () {
    expect(fn () => (new CreateNewUser)->create([
        'name' => fake()->name(),
        'email' => fake()->safeEmail(),
        'password' => '123',
        'password_confirmation' => '123',
    ]))->toThrow(ValidationException::class);
});

it('creates user with a long accented name', function () {
    $name = 'José joão Jeśco Bälièl Apollion Frinkles';
    $password = 'verysecure123';

    $user = (new CreateNewUser)->create([
        'name' => $name,
        'email' => fake()->safeEmail(),
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    expect($user->name)->toBe($name);
});
