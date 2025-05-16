<?php

namespace Database\Seeders;

use App\Models\Bike;
use App\Models\User;
use Illuminate\Database\Seeder;

class BikeSeeder extends Seeder
{
    public function run()
    {
        // Obtener solo usuarios visitantes
        $visitors = User::where('role', 'visitante')->get();

        // Datos de ejemplo para bicicletas
        $bikesData = [
            ['brand' => 'Trek', 'color' => 'Rojo', 'model' => 'Marlin 5'],
            ['brand' => 'Specialized', 'color' => 'Negro', 'model' => 'Rockhopper'],
            ['brand' => 'Giant', 'color' => 'Azul', 'model' => 'Talon 3'],
            ['brand' => 'Scott', 'color' => 'Verde', 'model' => 'Scale'],
        ];

        // Asignar 1-2 bicicletas por visitante
        $visitors->each(function ($visitor) use ($bikesData) {
            Bike::create([
                'user_id' => $visitor->id,
                ...$bikesData[array_rand($bikesData)] // Asigna datos aleatorios
            ]);

            // 50% de probabilidad de segunda bicicleta
            if (rand(0, 1)) {
                Bike::create([
                    'user_id' => $visitor->id,
                    ...$bikesData[array_rand($bikesData)]
                ]);
            }
        });
    }
}
