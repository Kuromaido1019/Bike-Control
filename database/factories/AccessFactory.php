<?php

namespace Database\Factories;

use App\Models\Bike;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccessFactory extends Factory
{
    public function definition()
    {
        $entrance = $this->faker->dateTimeBetween('-1 month', 'now');
        $exit = (rand(0, 3) > 1) ? $this->faker->dateTimeBetween($entrance, '+8 hours') : null;

        return [
            'user_id' => User::where('role', 'visitante')->inRandomOrder()->first()->id,
            'guard_id' => User::where('role', 'guardia')->inRandomOrder()->first()->id,
            'bike_id' => Bike::inRandomOrder()->first()->id,
            'entrance_time' => $entrance,
            'exit_time' => $exit,
            'observation' => $this->faker->optional(0.3)->sentence()
        ];
    }
}
