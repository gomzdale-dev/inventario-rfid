<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamamos al seeder que lee tu archivo inserts.sql
        $this->call([
            ActivosFijosSeeder::class,
        ]);
    }
}