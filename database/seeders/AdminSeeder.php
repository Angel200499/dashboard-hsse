<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Super Administrator',
                'email' => 'admin@pge.com',
                'password' => Hash::make('password'),
                'role' => 'Admin HSSE',
                'fungsi' => 'HSSE',
                'is_active' => true,
            ]
        );
    }
}
