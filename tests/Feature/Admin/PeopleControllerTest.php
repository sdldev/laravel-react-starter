<?php

declare(strict_types=1);

use App\Models\People;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
});

it('can display people index page', function (): void {
    People::factory()->count(3)->create();

    actingAs($this->admin)
        ->get(route('admin.people.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/People/Index')
            ->has('peoples.data', 3)
        );
});

it('can display people create page', function (): void {
    actingAs($this->admin)
        ->get(route('admin.people.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/People/Create')
        );
});

it('can create a new people', function (): void {
    $peopleData = [
        'name' => 'Test People',
        'email' => 'test.people@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'phone' => '+62 812 3456 7890',
        'address' => 'Test Address',
        'is_active' => true,
    ];

    actingAs($this->admin)
        ->post(route('admin.people.store'), $peopleData)
        ->assertRedirect(route('admin.people.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('peoples', [
        'name' => 'Test People',
        'email' => 'test.people@example.com',
    ]);
});

it('validates required fields when creating people', function (): void {
    actingAs($this->admin)
        ->post(route('admin.people.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});

it('validates unique email when creating people', function (): void {
    $existingPeople = People::factory()->create();

    actingAs($this->admin)
        ->post(route('admin.people.store'), [
            'name' => 'Test People',
            'email' => $existingPeople->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertSessionHasErrors(['email']);
});

it('can display people show page', function (): void {
    $people = People::factory()->create();

    actingAs($this->admin)
        ->get(route('admin.people.show', $people))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/People/Show')
            ->has('people')
        );
});

it('can display people edit page', function (): void {
    $people = People::factory()->create();

    actingAs($this->admin)
        ->get(route('admin.people.edit', $people))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/People/Edit')
            ->has('people')
        );
});

it('can update people', function (): void {
    $people = People::factory()->create();

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'phone' => '+62 812 9999 9999',
        'address' => 'Updated Address',
        'is_active' => true,
    ];

    actingAs($this->admin)
        ->put(route('admin.people.update', $people), $updateData)
        ->assertRedirect(route('admin.people.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('peoples', [
        'id' => $people->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

it('can update people without changing password', function (): void {
    $people = People::factory()->create();
    $originalPassword = $people->password;

    actingAs($this->admin)
        ->put(route('admin.people.update', $people), [
            'name' => $people->name,
            'email' => $people->email,
            'password' => '',
            'password_confirmation' => '',
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.people.index'));

    expect($people->fresh()->password)->toBe($originalPassword);
});

it('can delete people', function (): void {
    $people = People::factory()->create();

    actingAs($this->admin)
        ->delete(route('admin.people.destroy', $people))
        ->assertRedirect(route('admin.people.index'))
        ->assertSessionHas('success');

    assertDatabaseMissing('peoples', [
        'id' => $people->id,
    ]);
});
