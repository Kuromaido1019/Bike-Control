<?php

namespace Database\Seeders;

use App\Models\Access;
use App\Models\User;
use App\Models\Bike;
use Illuminate\Database\Seeder;

class AccessSeeder extends Seeder
{
    public function run()
    {
        // Verifica que existan los recursos necesarios
        if (User::where('role', 'visitante')->exists() &&
            User::where('role', 'guardia')->exists() &&
            Bike::exists()) {

            Access::factory()->count(50)->create();
        } else {
            $this->command->error('No hay suficientes usuarios o bicicletas para crear accesos!');
            $this->command->info('Ejecuta primero UsersTableSeeder y BikeSeeder');
        }
    }
}
