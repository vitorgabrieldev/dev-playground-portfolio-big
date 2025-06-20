<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomerPayoutMethodSeeder extends Seeder
{
    public function run()
    {
        \App\Models\CustomerPayoutMethod::factory(20)->create();
    }
} 