<?php

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

function getRuleObject(): object
{
    return new class
    {
        use PasswordValidationRules;

        public function rules(): array
        {
            return $this->passwordRules();
        }
    };
}

it('provides the default password validation rules', function () {
    $object = new class
    {
        use PasswordValidationRules;

        public function rules(): array
        {
            return $this->passwordRules();
        }
    };

    $rules = $object->rules();

    expect($rules)->toBeArray()
        ->and($rules)->toContain('required')
        ->and($rules)->toContain('string')
        ->and($rules)->toContain('confirmed');

    $passwordRule = collect($rules)->first(fn ($rule) => $rule instanceof Password);
    expect($passwordRule)->toBeInstanceOf(Password::class);
});

it('passes validation for a compliant password', function () {
    $object = new class
    {
        use PasswordValidationRules;

        public function rules(): array
        {
            return $this->passwordRules();
        }
    };

    $data = [
        'password' => 'ValidPass123!',
        'password_confirmation' => 'ValidPass123!',
    ];

    $validator = Validator::make($data, [
        'password' => $object->rules(),
    ]);

    expect($validator->fails())->toBeFalse();
});
it('fails when password is missing', function () {
    $data = ['password_confirmation' => 'AnyValue'];
    $validator = Validator::make($data, ['password' => getRuleObject()->rules()]);
    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue();
});

it('fails when password and confirmation mismatch', function () {
    $data = [
        'password' => 'ValidPass123!',
        'password_confirmation' => 'DifferentPass456!',
    ];
    $validator = Validator::make($data, ['password' => getRuleObject()->rules()]);
    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue();
});

it('fails with weak passwords', function ($weakPassword) {
    $data = [
        'password' => $weakPassword,
        'password_confirmation' => $weakPassword,
    ];
    $validator = Validator::make($data, ['password' => getRuleObject()->rules()]);
    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue();
})->with([
    'too short' => ['abc123'],
]);
