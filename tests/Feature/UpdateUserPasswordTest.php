<?php

use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('updates password when current password is correct', function () {
    $oldPassword = 'OldPassword123!';
    $user = User::factory()->create([
        'password' => bcrypt($oldPassword),
    ]);

    $this->actingAs($user);

    $newPassword = 'NewPassword456!';
    $input = [
        'current_password' => $oldPassword,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ];

    $action = new UpdateUserPassword;
    $action->update($user, $input);

    $user->refresh();
    expect(Hash::check($newPassword, $user->password))->toBeTrue();
    expect($user->password)->not->toEqual(bcrypt($oldPassword));
});

it('throws if current password is incorrect', function () {
    $user = User::factory()->create([
        'password' => bcrypt('OldPassword123!'),
    ]);

    $this->actingAs($user);

    $input = [
        'current_password' => 'WrongPassword!',
        'password' => 'NewPassword456!',
        'password_confirmation' => 'NewPassword456!',
    ];

    expect(fn () => (new UpdateUserPassword)->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws if new password is too weak', function () {
    $user = User::factory()->create([
        'password' => bcrypt('ValidPassword123'),
    ]);

    $this->actingAs($user);

    $input = [
        'current_password' => 'ValidPassword123',
        'password' => '123',
        'password_confirmation' => '123',
    ];

    expect(fn () => (new UpdateUserPassword)->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws if password confirmation does not match', function () {
    $user = User::factory()->create([
        'password' => bcrypt('SecurePass321!'),
    ]);

    $this->actingAs($user);

    $input = [
        'current_password' => 'SecurePass321!',
        'password' => 'AnotherSecure456!',
        'password_confirmation' => 'Mismatch456!',
    ];

    expect(fn () => (new UpdateUserPassword)->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws if password fields are missing', function () {
    $user = User::factory()->create([
        'password' => bcrypt('SecureBase123'),
    ]);

    $this->actingAs($user);

    $input = ['current_password' => 'SecureBase123'];

    expect(fn () => (new UpdateUserPassword)->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('rejects a variety of weak passwords', function ($weak) {
    $user = User::factory()->create([
        'password' => bcrypt('CurrentStrong123'),
    ]);

    $this->actingAs($user);

    $input = [
        'current_password' => 'CurrentStrong123',
        'password' => $weak,
        'password_confirmation' => $weak,
    ];

    expect(fn () => (new UpdateUserPassword)->update($user, $input))
        ->toThrow(ValidationException::class);
})->with([
    'too short' => ['abc'],
]);
