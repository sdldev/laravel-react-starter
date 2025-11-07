<?php

declare(strict_types=1);

use App\Models\People;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->people = People::factory()->create([
        'name' => 'Test People',
        'email' => 'test@example.com',
    ]);
});

it('can display profile edit page', function (): void {
    actingAs($this->people, 'peoples')
        ->get(route('peoples.profile.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Peoples/Profile/Edit')
            ->has('people')
        );
});

it('can update own profile', function (): void {
    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'phone' => '+62 812 9999 9999',
        'address' => 'Updated Address',
    ];

    actingAs($this->people, 'peoples')
        ->patch(route('peoples.profile.update'), $updateData)
        ->assertRedirect(route('peoples.profile.edit'))
        ->assertSessionHas('success');

    assertDatabaseHas('peoples', [
        'id' => $this->people->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

it('validates required fields when updating profile', function (): void {
    actingAs($this->people, 'peoples')
        ->patch(route('peoples.profile.update'), [
            'name' => '',
            'email' => '',
        ])
        ->assertSessionHasErrors(['name', 'email']);
});

it('validates unique email when updating profile', function (): void {
    $anotherPeople = People::factory()->create();

    actingAs($this->people, 'peoples')
        ->patch(route('peoples.profile.update'), [
            'name' => 'Test Name',
            'email' => $anotherPeople->email,
        ])
        ->assertSessionHasErrors(['email']);
});

it('can update profile with same email', function (): void {
    actingAs($this->people, 'peoples')
        ->patch(route('peoples.profile.update'), [
            'name' => 'Updated Name',
            'email' => $this->people->email, // Same email
            'phone' => '+62 812 9999 9999',
        ])
        ->assertRedirect(route('peoples.profile.edit'))
        ->assertSessionHas('success');

    assertDatabaseHas('peoples', [
        'id' => $this->people->id,
        'name' => 'Updated Name',
    ]);
});
