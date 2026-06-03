<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test admin',
            'email' => 'admin@email.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@email.com',
            'password' => bcrypt('usertest'),
            'email_verified_at' => now(),
        ]);
    }
}
