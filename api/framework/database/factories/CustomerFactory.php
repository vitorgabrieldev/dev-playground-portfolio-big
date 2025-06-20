<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    // Defina o model se existir, ex: protected $model = Customer::class;

    public function definition()
    {
        return [
            'uuid' => (string) Str::uuid(),
            'fullname' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('123456'),
            'avatar' => $this->faker->boolean ? null : 'storage/_default/avatar-' . $this->faker->numberBetween(1, 10) . '.jpg',
            'bio' => $this->faker->optional()->text(200),
            'document' => $this->faker->optional()->cpf(false),
            'birthdate' => $this->faker->optional()->date(),
            'gender' => $this->faker->optional()->randomElement(['male', 'female', 'other']),
            'phone' => $this->faker->optional()->phoneNumber(),
            'whatsapp' => $this->faker->optional()->phoneNumber(),
            'website' => $this->faker->optional()->url(),
            'social_links' => null,
            'address_street' => $this->faker->optional()->streetName(),
            'address_number' => $this->faker->optional()->buildingNumber(),
            'address_complement' => $this->faker->optional()->secondaryAddress(),
            'address_neighborhood' => $this->faker->optional()->citySuffix(),
            'address_city' => $this->faker->optional()->city(),
            'address_state' => $this->faker->optional()->stateAbbr(),
            'address_zipcode' => $this->faker->optional()->postcode(),
            'address_country' => $this->faker->optional()->country(),
            'email_verified_at' => now(),
            'phone_verified_at' => null,
            'is_active' => $this->faker->boolean(90),
            'is_blocked' => false,
            'blocked_reason' => null,
            'last_login_at' => null,
            'last_ip' => $this->faker->optional()->ipv4(),
            'register_ip' => $this->faker->optional()->ipv4(),
            'register_source' => $this->faker->optional()->randomElement(['web', 'mobile', 'import']),
            'language' => 'pt-BR',
            'notification_preferences' => null,
            'referral_code' => null,
            'referred_by' => null,
            'custom_data' => null,
            'cpf_front_image' => null,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 