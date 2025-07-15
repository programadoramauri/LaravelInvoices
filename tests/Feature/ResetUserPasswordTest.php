<?php

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('resets the user password with valid data', function () {
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
});
it('fails to reset password then confirmation does not match', function () {
    $user = User::factory()->create([
        'password' => bcrypt('oldpassword'),
    ]);

    $input = [
        'password' => 'NewStrongPass123!',
        'password_confirmation' => 'WrongConfirmation',
    ];

    $action = new ResetUserPassword;

    expect(fn () => $action->reset($user, $input))
        ->toThrow(ValidationException::class);
});
it('fails to reset password if too weak', function () {
    $user = User::factory()->create([
        'password' => bcrypt('oldpassword'),
    ]);

    $input = [
        'password' => '123',
        'password_confirmation' => '123',
    ];

    $action = new ResetUserPassword;

    expect(fn () => $action->reset($user, $input))
        ->toThrow(ValidationException::class);
});
