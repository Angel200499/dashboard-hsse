<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class IqbaalSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'iqbaal'],
            [
                'name' => 'Iqbaal',
                'email' => 'iqbaal@pge.com',
                'password' => Hash::make('iqbaal123'),
                'role' => 'Admin HSSE',
                'fungsi' => 'HSSE',
                'is_active' => true,
            ]
        );
    }
}
