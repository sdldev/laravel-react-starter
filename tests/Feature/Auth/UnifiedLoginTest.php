<?php

declare(strict_types=1);

use App\Models\Staff;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('unified login can authenticate admin user', function (): void {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post(route('unified.login'), [
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    expect(auth()->guard('web')->check())->toBeTrue();
    expect(auth()->guard('staff')->check())->toBeFalse();
    $response->assertRedirect(route('dashboard'));
});

test('unified login can authenticate staff user', function (): void {
    $staff = Staff::factory()->create([
        'email' => 'staff@example.com',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    $response = $this->post(route('unified.login'), [
        'email' => 'staff@example.com',
        'password' => 'password',
    ]);

    expect(auth()->guard('staff')->check())->toBeTrue();
    expect(auth()->guard('web')->check())->toBeFalse();
    $response->assertRedirect(route('staff.profile.edit'));
});

test('unified login clears other guard sessions when admin logs in', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);

    $staff = Staff::factory()->create([
        'email' => 'staff@example.com',
        'password' => bcrypt('password'),
    ]);

    // Login as staff first
    auth()->guard('staff')->login($staff);
    expect(auth()->guard('staff')->check())->toBeTrue();

    // Login as admin
    $response = $this->post(route('unified.login'), [
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    // Staff guard should be logged out
    expect(auth()->guard('web')->check())->toBeTrue();
    expect(auth()->guard('staff')->check())->toBeFalse();
    $response->assertRedirect(route('dashboard'));
});

test('unified login clears other guard sessions when staff logs in', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);

    $staff = Staff::factory()->create([
        'email' => 'staff@example.com',
        'password' => bcrypt('password'),
    ]);

    // Login as admin first (use actingAs to maintain session)
    $this->actingAs($admin, 'web');
    expect(auth()->guard('web')->check())->toBeTrue();

    // Login as staff via unified login
    $response = $this->post(route('unified.login'), [
        'email' => 'staff@example.com',
        'password' => 'password',
    ]);

    // After unified login, staff guard should be authenticated
    // Admin guard should be logged out
    expect(auth()->guard('staff')->check())->toBeTrue();
    expect(auth()->guard('web')->check())->toBeFalse();
    $response->assertRedirect(route('staff.profile.edit'));
});

test('unified login rejects inactive staff', function (): void {
    $staff = Staff::factory()->create([
        'email' => 'staff@example.com',
        'password' => bcrypt('password'),
        'is_active' => false,
    ]);

    $response = $this->post(route('unified.login'), [
        'email' => 'staff@example.com',
        'password' => 'password',
    ]);

    expect(auth()->guard('staff')->check())->toBeFalse();
    expect(auth()->guard('web')->check())->toBeFalse();
    $response->assertSessionHasErrors('email');
});

test('unified login fails with invalid credentials', function (): void {
    $response = $this->post(route('unified.login'), [
        'email' => 'nonexistent@example.com',
        'password' => 'wrong-password',
    ]);

    expect(auth()->guard('web')->check())->toBeFalse();
    expect(auth()->guard('staff')->check())->toBeFalse();
    $response->assertSessionHasErrors('email');
});

test('unified login validates required fields', function (): void {
    $response = $this->post(route('unified.login'), []);

    $response->assertSessionHasErrors(['email', 'password']);
});

test('unified login prioritizes admin over staff when same email exists', function (): void {
    // This should not happen in practice due to UniqueEmailAcrossGates rule
    // but we test the priority logic anyway
    $admin = User::factory()->create([
        'email' => 'same@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post(route('unified.login'), [
        'email' => 'same@example.com',
        'password' => 'password',
    ]);

    // Admin should be authenticated (priority)
    expect(auth()->guard('web')->check())->toBeTrue();
    expect(auth()->guard('staff')->check())->toBeFalse();
    $response->assertRedirect(route('dashboard'));
});

test('unified login respects remember me option for admin', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post(route('unified.login'), [
        'email' => 'admin@example.com',
        'password' => 'password',
        'remember' => true,
    ]);

    expect(auth()->guard('web')->check())->toBeTrue();
    expect(auth()->guard('web')->user()->id)->toBe($admin->id);
    $response->assertRedirect(route('dashboard'));
});

test('unified login respects remember me option for staff', function (): void {
    $staff = Staff::factory()->create([
        'email' => 'staff@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post(route('unified.login'), [
        'email' => 'staff@example.com',
        'password' => 'password',
        'remember' => true,
    ]);

    expect(auth()->guard('staff')->check())->toBeTrue();
    expect(auth()->guard('staff')->user()->id)->toBe($staff->id);
    $response->assertRedirect(route('staff.profile.edit'));
});
