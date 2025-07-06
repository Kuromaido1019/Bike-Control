<?php

namespace Database\Seeders;

use App\Models\Access;
use App\Models\User;
use App\Models\Bike;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class LastVisitorsAccessSeeder extends Seeder
{
    public function run()
    {
        // Obtiene los últimos 5 usuarios tipo visitante
        $visitantes = User::where('role', 'visitante')->orderByDesc('id')->take(5)->get();
        $guardia = User::where('role', 'guardia')->inRandomOrder()->first();

        if ($visitantes->isEmpty() || !$guardia) {
            $this->command->error('No hay suficientes visitantes o guardias para crear accesos.');
            return;
        }

        foreach ($visitantes as $visitante) {
            $bike = $visitante->bikes()->inRandomOrder()->first();
            if (!$bike) {
                $this->command->info("El visitante {$visitante->name} no tiene bicicleta, se omite.");
                continue;
            }
            Access::create([
                'user_id' => $visitante->id,
                'guard_id' => $guardia->id,
                'bike_id' => $bike->id,
                'entrance_time' => Carbon::now()->subDays(rand(0, 10))->format('Y-m-d H:i:s'),
                'observation' => 'Ingreso generado por seeder',
            ]);
        }
        $this->command->info('Accesos generados para los últimos 5 visitantes.');
    }
}
