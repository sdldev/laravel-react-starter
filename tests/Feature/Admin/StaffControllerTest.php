<?php

declare(strict_types=1);

use App\Models\Staff;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
});

it('can display people index page', function (): void {
    Staff::factory()->count(3)->create();

    actingAs($this->admin)
        ->get(route('admin.staff.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Staff/Index')
            ->has('staffs.data', 3)
        );
});

it('can display people create page', function (): void {
    actingAs($this->admin)
        ->get(route('admin.staff.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Staff/Create')
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
        ->post(route('admin.staff.store'), $peopleData)
        ->assertRedirect(route('admin.staff.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('staffs', [
        'name' => 'Test People',
        'email' => 'test.people@example.com',
    ]);
});

it('validates required fields when creating people', function (): void {
    actingAs($this->admin)
        ->post(route('admin.staff.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});

it('validates unique email when creating people', function (): void {
    $existingPeople = Staff::factory()->create();

    actingAs($this->admin)
        ->post(route('admin.staff.store'), [
            'name' => 'Test People',
            'email' => $existingPeople->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertSessionHasErrors(['email']);
});

it('can display people show page', function (): void {
    $people = Staff::factory()->create();

    actingAs($this->admin)
        ->get(route('admin.staff.show', $people))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Staff/Show')
            ->has('staff')
        );
});

it('can display people edit page', function (): void {
    $people = Staff::factory()->create();

    actingAs($this->admin)
        ->get(route('admin.staff.edit', $people))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Staff/Edit')
            ->has('staff')
        );
});

it('can update people', function (): void {
    $people = Staff::factory()->create();

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'phone' => '+62 812 9999 9999',
        'address' => 'Updated Address',
        'is_active' => true,
    ];

    actingAs($this->admin)
        ->put(route('admin.staff.update', $people), $updateData)
        ->assertRedirect(route('admin.staff.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('staffs', [
        'id' => $people->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

it('can update people without changing password', function (): void {
    $people = Staff::factory()->create();
    $originalPassword = $people->password;

    actingAs($this->admin)
        ->put(route('admin.staff.update', $people), [
            'name' => $people->name,
            'email' => $people->email,
            'password' => '',
            'password_confirmation' => '',
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.staff.index'));

    expect($people->fresh()->password)->toBe($originalPassword);
});

it('can delete people', function (): void {
    $people = Staff::factory()->create();

    actingAs($this->admin)
        ->delete(route('admin.staff.destroy', $people))
        ->assertRedirect(route('admin.staff.index'))
        ->assertSessionHas('success');

    assertDatabaseMissing('staffs', [
        'id' => $people->id,
    ]);
});
