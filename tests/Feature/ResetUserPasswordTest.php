<?php

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('successfully resets the password with valid input', function () {
    $user = User::factory()->create([
        'password' => bcrypt('oldpassword'),
    ]);

    $newPassword = 'NewStrongPass123!';
    $input = [
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ];

    $action = new ResetUserPassword;
    $action->reset($user, $input);

    $user->refresh();

    expect(Hash::check($newPassword, $user->password))->toBeTrue();
    expect($user->password)->not->toEqual(bcrypt('oldpassword'));
});

it('throws when confirmation does not match', function () {
    $user = User::factory()->create([
        'password' => bcrypt('oldpassword'),
    ]);

    $input = [
        'password' => 'NewStrongPass123!',
        'password_confirmation' => 'WrongConfirmation',
    ];

    expect(fn () => (new ResetUserPassword)->reset($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws when password is too weak', function () {
    $user = User::factory()->create([
        'password' => bcrypt('oldpassword'),
    ]);

    $input = [
        'password' => '123',
        'password_confirmation' => '123',
    ];

    expect(fn () => (new ResetUserPassword)->reset($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws when password field is missing', function () {
    $user = User::factory()->create([
        'password' => bcrypt('oldpassword'),
    ]);

    $input = [
        'password_confirmation' => 'NewStrongPass123!',
    ];

    expect(fn () => (new ResetUserPassword)->reset($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws with various weak password formats', function ($weak) {
    $user = User::factory()->create([
        'password' => bcrypt('oldpassword'),
    ]);

    $input = [
        'password' => $weak,
        'password_confirmation' => $weak,
    ];

    expect(fn () => (new ResetUserPassword)->reset($user, $input))
        ->toThrow(ValidationException::class);
})->with([
    'too short' => ['abc'],
]);

it('returns validation error fields when password is invalid', function () {
    $user = User::factory()->create([
        'password' => bcrypt('oldpassword'),
    ]);

    try {
        (new ResetUserPassword)->reset($user, [
            'password' => 'abc123',
            'password_confirmation' => 'abc123',
        ]);
    } catch (ValidationException $e) {
        $fields = array_keys($e->errors());
        expect($fields)->toContain('password');
    }
});
