<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('creates a new user with valid input', function () {
    $action = new CreateNewUser;

    $name = fake()->name();
    $email = fake()->safeEmail();
    $password = 'SecurePassword1';

    $user = $action->create([
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect(Hash::check($password, $user->password))->toBeTrue();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => $email, 'name' => $name]);
});

it('fails when password confirmation does not match', function () {
    $password = fake()->password(8);
    expect(fn () => (new CreateNewUser)->create([
        'name' => fake()->name(),
        'email' => fake()->safeEmail(),
        'password' => $password,
        'password_confirmation' => $password.'diff',
    ]))->toThrow(ValidationException::class);
});

it('fails with duplicate email', function () {
    $email = fake()->safeEmail();
    User::factory()->create(['email' => $email]);

    expect(fn () => (new CreateNewUser)->create([
        'name' => fake()->name(),
        'email' => $email,
        'password' => 'SecurePassword1',
        'password_confirmation' => 'SecurePassword1',
    ]))->toThrow(ValidationException::class);
});

it('fails with invalid email format', function () {
    expect(fn () => (new CreateNewUser)->create([
        'name' => fake()->name(),
        'email' => 'invalid-email',
        'password' => 'SecurePassword1',
        'password_confirmation' => 'SecurePassword1',
    ]))->toThrow(ValidationException::class);
});

it('fails when required fields are missing', function () {
    expect(fn () => (new CreateNewUser)->create([]))
        ->toThrow(ValidationException::class)
        ->and(fn (ValidationException $e) => collect($e->errors())->keys()->sort()->all() === ['email', 'name', 'password']
        );
});

it('fails with weak passwords', function ($weakPassword) {
    expect(fn () => (new CreateNewUser)->create([
        'name' => fake()->name(),
        'email' => fake()->safeEmail(),
        'password' => $weakPassword,
        'password_confirmation' => $weakPassword,
    ]))->toThrow(ValidationException::class);
})->with([
    'too short' => ['123'],
]);

it('creates user with long accented name', function () {
    $name = 'José João Jeśco Bälièl Apollion Frinkles';
    $email = fake()->safeEmail();
    $password = 'SecureAccent123';

    $user = (new CreateNewUser)->create([
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    expect($user->name)->toBe($name);
    $this->assertDatabaseHas('users', ['email' => $email]);
});
