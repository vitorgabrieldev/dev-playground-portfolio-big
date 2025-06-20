<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProducerBalanceFactory extends Factory
{
    public function definition()
    {
        return [
            'producer_id' => 1, // Ajuste para relacionamento real
            'balance_available' => $this->faker->randomFloat(2, 0, 10000),
            'balance_blocked' => $this->faker->randomFloat(2, 0, 1000),
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }
} 