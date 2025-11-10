<?php

declare(strict_types=1);

use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Validator;

test('strong password passes with valid password', function (): void {
    $validator = Validator::make(
        ['password' => 'Password123'],
        ['password' => [new StrongPassword]]
    );

    expect($validator->passes())->toBeTrue();
});

test('strong password fails when too short', function (): void {
    $validator = Validator::make(
        ['password' => 'Pass1'],
        ['password' => [new StrongPassword]]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('password'))->toContain('minimal harus 8 karakter');
});

test('strong password fails without uppercase', function (): void {
    $validator = Validator::make(
        ['password' => 'password123'],
        ['password' => [new StrongPassword]]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('password'))->toContain('huruf besar');
});

test('strong password fails without lowercase', function (): void {
    $validator = Validator::make(
        ['password' => 'PASSWORD123'],
        ['password' => [new StrongPassword]]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('password'))->toContain('huruf kecil');
});

test('strong password fails without numbers', function (): void {
    $validator = Validator::make(
        ['password' => 'PasswordABC'],
        ['password' => [new StrongPassword]]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('password'))->toContain('angka');
});

test('strong password with custom minimum length', function (): void {
    $validator = Validator::make(
        ['password' => 'Pass123'],
        ['password' => [new StrongPassword(minLength: 10)]]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('password'))->toContain('minimal harus 10 karakter');
});

test('strong password requires special characters when configured', function (): void {
    $validator = Validator::make(
        ['password' => 'Password123'],
        ['password' => [new StrongPassword(requireSpecialChars: true)]]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('password'))->toContain('karakter spesial');
});

test('strong password passes with special characters when required', function (): void {
    $validator = Validator::make(
        ['password' => 'Password123!'],
        ['password' => [new StrongPassword(requireSpecialChars: true)]]
    );

    expect($validator->passes())->toBeTrue();
});
