<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\People;
use Illuminate\Database\Seeder;

final class PeopleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default people account for testing
        People::factory()->create([
            'name' => 'Test People',
            'email' => 'people@example.com',
            'password' => 'password',
            'phone' => '+62 812 3456 7890',
            'address' => 'Jl. Test No. 123, Jakarta',
        ]);

        // Create additional random peoples for testing
        People::factory(10)->create();

        // Create some inactive peoples
        People::factory(3)->inactive()->create();
    }
}
