<?php

use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('updates the user password when current password is correct', function () {
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
});
it('fails to update password when current password is incorrect', function () {
    $oldPassword = 'OldPassword123!';
    $user = User::factory()->create([
        'password' => bcrypt($oldPassword),
    ]);

    $this->actingAs($user);

    $newPassword = 'NewPassword456!';

    $input = [
        'current_password' => 'WrongPassword123!',
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ];

    $action = new UpdateUserPassword;
    expect(fn () => $action->update($user, $input))
        ->toThrow(ValidationException::class);
});
it('fails to update password when new password is weak', function () {
    $user = User::factory()->create([
        'password' => bcrypt('MySecurePass123'),
    ]);

    $this->actingAs($user);

    $input = [
        'current_password' => 'MySecurePass123',
        'password' => '123',
        'password_confirmation' => '123',
    ];

    $action = new UpdateUserPassword;

    expect(fn () => $action->update($user, $input))
        ->toThrow(ValidationException::class);
});
