<?php

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('updates user profile information when email does not change', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $input = [
        'name' => 'New Name',
        'email' => 'old@example.com',
    ];

    $action = new UpdateUserProfileInformation;
    $action->update($user, $input);

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('old@example.com');
});
it('updates user profile and resets verification when email change for verified user', function () {
    Notification::fake();

    $user = new class extends User implements MustVerifyEmail
    {
        public function getTable()
        {
            return 'users';
        }

        public function hasVerifiedEmail()
        {
            return true;
        }

        public function sendEmailVerificationNotification()
        {
            Notification::send($this, new VerifyEmail);
        }

        public function getEmailForVerification()
        {
            return $this->email;
        }
    };

    $user->forceFill([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'password' => fake()->password(8),
        'email_verified_at' => now(),
    ])->save();

    $input = [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ];

    $this->actingAs($user);

    $action = new UpdateUserProfileInformation;
    $action->update($user, $input);

    $user->refresh();
    expect($user->name)->toBe('New Name')
        ->and($user->email)
        ->and($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('throws validation error when name is missing', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $input = [
        'email' => 'test@example.com',
    ];

    $action = new UpdateUserProfileInformation;
    expect(fn () => $action->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation error when email is invalid', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $input = [
        'name' => fake()->name(),
        'email' => 'invalid-email',
    ];

    $action = new UpdateUserProfileInformation;
    expect(fn () => $action->update($user, $input))
        ->toThrow(ValidationException::class);
});
it('throws validation when email is already taken by another user', function () {
    $existing = User::factory()->create([
        'email' => 'taken@example.com',
    ]);

    $user = User::factory()->create([
        'email' => 'original@example.com',
    ]);

    $this->actingAs($user);

    $input = [
        'name' => fake()->name(),
        'email' => 'taken@example.com',
    ];

    $action = new UpdateUserProfileInformation;
    expect(fn () => $action->update($user, $input))
        ->toThrow(ValidationException::class);
});
