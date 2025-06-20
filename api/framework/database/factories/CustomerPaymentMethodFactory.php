<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerPaymentMethodFactory extends Factory
{
    public function definition()
    {
        return [
            'customer_id' => 1, // Ajuste para relacionamento real
            'type' => $this->faker->randomElement(['credit_card', 'debit_card']),
            'label' => $this->faker->optional()->word(),
            'last_digits' => $this->faker->numerify('####'),
            'brand' => $this->faker->creditCardType(),
            'holder_name' => $this->faker->name(),
            'expiration' => $this->faker->creditCardExpirationDateString(),
            'bank_data' => null,
            'is_default' => $this->faker->boolean(20),
            'is_active' => $this->faker->boolean(90),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 