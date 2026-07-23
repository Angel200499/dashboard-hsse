<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Menjalankan semua seeder yang dibutuhkan secara berurutan.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            ManagerSeeder::class,
            TestUsersSeeder::class,
        ]);
    }
}
