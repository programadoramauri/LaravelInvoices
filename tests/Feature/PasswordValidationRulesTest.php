<?php

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

it('returns the default password validation rules', function () {
    $object = new class
    {
        use PasswordValidationRules;

        public function getRules(): array
        {
            return $this->passwordRules();
        }
    };

    $rules = $object->getRules();

    expect($rules)->toBeArray()
        ->and($rules)->toContain('required')
        ->and($rules)->toContain('string')
        ->and($rules)->toContain('confirmed');

    $hasPasswordRule = collect($rules)->first(fn ($rule) => $rule instanceof Password);
    expect($hasPasswordRule)->toBeInstanceOf(Password::class);
});
it('validates a password using the trait rules', function () {
    $object = new class
    {
        use PasswordValidationRules;

        public function getRules(): array
        {
            return $this->passwordRules();
        }
    };

    $data = [
        'password' => 'ValidPass123!',
        'password_confirmation' => 'ValidPass123!',
    ];

    $validator = Validator::make($data, ['password' => $object->getRules()]);
    expect($validator->fails())->toBeFalse();
});
