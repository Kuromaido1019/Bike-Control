<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Access;
use App\Models\Profile;
use App\Models\Bikes;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            BikeSeeder::class,
            ProfileSeeder::class,
            AccessSeeder::class
        ]);

        // User::factory(10)->create();
        //Profile::factory()->count(15)->create();
        /*
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        */
    }
}
