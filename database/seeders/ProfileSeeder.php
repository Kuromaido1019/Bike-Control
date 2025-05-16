<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    public function run()
    {
        // Obtener solo usuarios visitantes
        $visitors = User::where('role', 'visitante')->get();

        $visitors->each(function ($visitor) {
            Profile::create([
                'user_id' => $visitor->id,
                'phone' => $this->generateChileanPhoneNumber(),
                'rut' => $this->generateRUT(),
                'birth_date' => fake()->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
                'career' => fake()->randomElement([
                    'Ingeniería Informática',
                    'Gastronomía',
                    'Enfermería',
                    'Medicina',
                    null // 20% de probabilidad de no tener carrera
                ])
            ]);
        });
    }

    private function generateChileanPhoneNumber(): string
    {
        return '9' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    private function generateRUT(): string
    {
        $number = rand(1000000, 25000000);
        $dv = $this->calculateDV($number);
        return "$number-$dv";
    }

    private function calculateDV(int $rut): string
    {
        $s = 1;
        for ($m = 0; $rut != 0; $rut /= 10) {
            $s = ($s + $rut % 10 * (9 - $m++ % 6)) % 11;
        }
        return chr($s ? $s + 47 : 75); // 75 = 'K'
    }
}
