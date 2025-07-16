<?php

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new UpdateUserProfileInformation;
});

it('updates name only when email stays the same', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $input = [
        'name' => 'New Name',
        'email' => 'old@example.com',
    ];

    $this->action->update($user, $input);

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('old@example.com');
});

it('resets email_verified_at and sends notification when verified user changes email', function () {
    Notification::fake();

    // Create a user that implements MustVerifyEmail
    $user = new class extends User implements MustVerifyEmail
    {
        public function getTable()
        {
            return 'users';
        }

        public function sendEmailVerificationNotification()
        {
            Notification::send($this, new VerifyEmail);
        }
    };

    $user->forceFill([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'password' => bcrypt('irrelevant'),
        'email_verified_at' => now(),
    ])->save();

    $input = [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ];

    $this->action->update($user, $input);

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('new@example.com')
        ->and($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('does not send email verification if email is unchanged for verified user', function () {
    Notification::fake();

    $user = new class extends User implements MustVerifyEmail
    {
        public function getTable()
        {
            return 'users';
        }

        public function sendEmailVerificationNotification()
        {
            Notification::send($this, new VerifyEmail);
        }
    };

    $user->forceFill([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'password' => bcrypt('irrelevant'),
        'email_verified_at' => now(),
    ])->save();

    $input = [
        'name' => 'Brand New Name',
        'email' => 'old@example.com',
    ];

    $this->action->update($user, $input);

    Notification::assertNothingSent();
});

it('throws when name is missing', function () {
    $user = User::factory()->create();

    $input = ['email' => 'test@example.com'];

    expect(fn () => $this->action->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws when email format is invalid', function () {
    $user = User::factory()->create();

    $input = [
        'name' => 'Valid Name',
        'email' => 'not-an-email',
    ];

    expect(fn () => $this->action->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws when email is already taken by another user', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $user = User::factory()->create(['email' => 'me@example.com']);

    $input = [
        'name' => 'My Name',
        'email' => 'taken@example.com',
    ];

    expect(fn () => $this->action->update($user, $input))
        ->toThrow(ValidationException::class);
});
