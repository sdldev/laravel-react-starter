<?php

declare(strict_types=1);

use App\Models\Staff;
use App\Models\User;
use App\Rules\UniqueEmailAcrossGates;
use Illuminate\Support\Facades\Validator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('unique email across gates passes when email is unique', function (): void {
    $validator = Validator::make(
        ['email' => 'unique@example.com'],
        ['email' => [new UniqueEmailAcrossGates()]]
    );

    expect($validator->passes())->toBeTrue();
});

test('unique email across gates fails when email exists in users table', function (): void {
    User::factory()->create(['email' => 'admin@example.com']);

    $validator = Validator::make(
        ['email' => 'admin@example.com'],
        ['email' => [new UniqueEmailAcrossGates()]]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('email'))->toContain('sudah digunakan');
});

test('unique email across gates fails when email exists in staffs table', function (): void {
    Staff::factory()->create(['email' => 'staff@example.com']);

    $validator = Validator::make(
        ['email' => 'staff@example.com'],
        ['email' => [new UniqueEmailAcrossGates()]]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('email'))->toContain('sudah digunakan');
});

test('unique email across gates ignores specified record when updating', function (): void {
    $user = User::factory()->create(['email' => 'admin@example.com']);

    // Should pass when updating the same user
    $validator = Validator::make(
        ['email' => 'admin@example.com'],
        ['email' => [new UniqueEmailAcrossGates('users', $user->id)]]
    );

    expect($validator->passes())->toBeTrue();
});

test('unique email across gates fails when email exists in different table even with ignore', function (): void {
    $admin = User::factory()->create(['email' => 'same@example.com']);
    $staff = Staff::factory()->create(['email' => 'other@example.com']);

    // Staff trying to update email to admin's email should fail
    $validator = Validator::make(
        ['email' => 'same@example.com'],
        ['email' => [new UniqueEmailAcrossGates('staffs', $staff->id)]]
    );

    expect($validator->fails())->toBeTrue();
});

test('unique email across gates allows same email for ignored record in correct table', function (): void {
    $staff = Staff::factory()->create(['email' => 'staff@example.com']);

    // Staff updating their own email should pass
    $validator = Validator::make(
        ['email' => 'staff@example.com'],
        ['email' => [new UniqueEmailAcrossGates('staffs', $staff->id)]]
    );

    expect($validator->passes())->toBeTrue();
});
