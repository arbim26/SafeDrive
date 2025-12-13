<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Jika perlu data dummy
        \App\Models\User::factory(10)->create();
    }
}