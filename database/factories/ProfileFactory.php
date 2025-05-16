<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::where('role', 'visitante')->inRandomOrder()->first()->id,
            'phone' => $this->generateChileanMobileNumber(),
            'alt_phone' => $this->faker->boolean(70) ? $this->generateChileanMobileNumber() : null,
            'rut' => $this->generateValidRUT(),
            'birth_date' => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'career' => $this->faker->randomElement([
                'Ingeniería Informática',
                'Gastronomía',
                'Enfermería',
                'Medicina',
                'Derecho',
                'Arquitectura',
                'Administración',
                null  // 20% de probabilidad de no tener carrera
            ])
        ];
    }

    /**
     * Genera un número móvil chileno válido (9 + 8 dígitos)
     */
    private function generateChileanMobileNumber(): string
    {
        return '9' . $this->faker->numerify('########');
    }

    /**
     * Genera un RUT chileno válido con dígito verificador
     */
    private function generateValidRUT(): string
    {
        $number = $this->faker->numberBetween(4_000_000, 25_000_000);
        $dv = $this->calculateDV($number);

        return number_format($number, 0, '', '') . '-' . $dv;
    }

    /**
     * Calcula el dígito verificador para RUT chileno
     */
    private function calculateDV(int $rut): string
    {
        $s = 1;
        for ($m = 0; $rut != 0; $rut /= 10) {
            $s = ($s + $rut % 10 * (9 - $m++ % 6)) % 11;
        }
        return chr($s ? $s + 47 : 75); // 75 = 'K'
    }

    /**
     * Configuración para usuarios específicos (opcional)
     */
    public function forUser(User $user): self
    {
        return $this->state([
            'user_id' => $user->id,
            'birth_date' => $this->faker->dateTimeBetween('-40 years', '-20 years')->format('Y-m-d'),
        ]);
    }
}
