<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerPayoutMethodFactory extends Factory
{
    public function definition()
    {
        return [
            'customer_id' => 1, // Ajuste para relacionamento real
            'label' => $this->faker->optional()->word(),
            'bank_name' => $this->faker->company(),
            'bank_code' => $this->faker->numerify('###'),
            'agency' => $this->faker->numerify('####'),
            'account' => $this->faker->numerify('########'),
            'account_type' => $this->faker->randomElement(['corrente', 'poupança']),
            'holder_name' => $this->faker->name(),
            'holder_document' => $this->faker->cpf(false),
            'is_default' => $this->faker->boolean(20),
            'is_active' => $this->faker->boolean(90),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 