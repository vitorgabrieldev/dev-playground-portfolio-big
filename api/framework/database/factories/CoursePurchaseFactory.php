<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CoursePurchase>
 */
class CoursePurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 29.90, 299.90);
        $platformFee = $price * 0.15; // 15% de taxa da plataforma
        $creatorRevenue = $price - $platformFee;
        
        return [
            'uuid' => Str::uuid(),
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'price_paid' => $price,
            'platform_fee' => $platformFee,
            'creator_revenue' => $creatorRevenue,
            'payment_status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'payment_method' => $this->faker->randomElement(['credit_card', 'debit_card', 'pix', 'bank_transfer', 'paypal']),
            'transaction_id' => Str::random(20),
            'purchased_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'access_expires_at' => null, // Acesso vitalício
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the purchase is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'completed',
            'purchased_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the purchase is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'pending',
            'purchased_at' => null,
        ]);
    }
} 