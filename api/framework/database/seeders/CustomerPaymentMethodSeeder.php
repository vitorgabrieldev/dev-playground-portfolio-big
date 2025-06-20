<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomerPaymentMethodSeeder extends Seeder
{
    public function run()
    {
        \App\Models\CustomerPaymentMethod::factory(30)->create();
    }
} 