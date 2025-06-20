<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProducerBalanceSeeder extends Seeder
{
    public function run()
    {
        \App\Models\ProducerBalance::factory(10)->create();
    }
} 