<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Admin Principal',
                'email' => 'admin@bikecontrol.com',
                'password' => Hash::make('Admin123'),
                'role' => 'admin'
            ],
            [
                'name' => 'Guardia José Pérez',
                'email' => 'guardia1@bikecontrol.com',
                'password' => Hash::make('Guardia123'),
                'role' => 'guardia'
            ],
            [
                'name' => 'Visitante Ana López',
                'email' => 'visitante1@test.com',
                'password' => Hash::make('Visitante123'),
                'role' => 'visitante'
            ]
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        // Opcional: Crear usuarios aleatorios con Factory
        User::factory()->count(5)->create(['role' => 'visitante']);
        User::factory()->count(2)->create(['role' => 'guardia']);
    }
}
