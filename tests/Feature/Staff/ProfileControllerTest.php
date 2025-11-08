<?php

declare(strict_types=1);

use App\Models\Staff;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->people = Staff::factory()->create([
        'name' => 'Test People',
        'email' => 'test@example.com',
    ]);
});

it('can display profile edit page', function (): void {
    actingAs($this->people, 'staff')
        ->get(route('staff.profile.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Staff/Profile/Edit')
            ->has('staff')
        );
});

it('can update own profile', function (): void {
    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'phone' => '+62 812 9999 9999',
        'address' => 'Updated Address',
    ];

    actingAs($this->people, 'staff')
        ->patch(route('staff.profile.update'), $updateData)
        ->assertRedirect(route('staff.profile.edit'))
        ->assertSessionHas('success');

    assertDatabaseHas('staffs', [
        'id' => $this->people->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

it('validates required fields when updating profile', function (): void {
    actingAs($this->people, 'staff')
        ->patch(route('staff.profile.update'), [
            'name' => '',
            'email' => '',
        ])
        ->assertSessionHasErrors(['name', 'email']);
});

it('validates unique email when updating profile', function (): void {
    $anotherPeople = Staff::factory()->create();

    actingAs($this->people, 'staff')
        ->patch(route('staff.profile.update'), [
            'name' => 'Test Name',
            'email' => $anotherPeople->email,
        ])
        ->assertSessionHasErrors(['email']);
});

it('can update profile with same email', function (): void {
    actingAs($this->people, 'staff')
        ->patch(route('staff.profile.update'), [
            'name' => 'Updated Name',
            'email' => $this->people->email, // Same email
            'phone' => '+62 812 9999 9999',
        ])
        ->assertRedirect(route('staff.profile.edit'))
        ->assertSessionHas('success');

    assertDatabaseHas('staffs', [
        'id' => $this->people->id,
        'name' => 'Updated Name',
    ]);
});
