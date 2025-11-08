<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;

final class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default staff account for testing
        Staff::factory()->create([
            'name' => 'Test Staff',
            'email' => 'staff@example.com',
            'password' => 'password',
            'phone' => '+62 812 3456 7890',
            'address' => 'Jl. Test No. 123, Jakarta',
        ]);

        // Create additional random staff for testing
        Staff::factory(10)->create();

        // Create some inactive staff
        Staff::factory(3)->inactive()->create();
    }
}
