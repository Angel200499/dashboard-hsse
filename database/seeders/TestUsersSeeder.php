<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Admin Operation',
                'username' => 'admin_op',
                'password' => Hash::make('password123'),
                'role' => 'Admin Function',
                'fungsi' => 'Operation',
                'is_active' => true,
            ],
            [
                'name' => 'Admin Maintenance',
                'username' => 'admin_mt',
                'password' => Hash::make('password123'),
                'role' => 'Admin Function',
                'fungsi' => 'Maintenance',
                'is_active' => true,
            ],
            [
                'name' => 'Admin HSSE (Fungsi)',
                'username' => 'admin_hsse_f',
                'password' => Hash::make('password123'),
                'role' => 'Admin Function',
                'fungsi' => 'HSSE',
                'is_active' => true,
            ],
            [
                'name' => 'Admin Business Support',
                'username' => 'admin_bs',
                'password' => Hash::make('password123'),
                'role' => 'Admin Function',
                'fungsi' => 'Business Support',
                'is_active' => true,
            ]
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['username' => $user['username']],
                $user
            );
        }
    }
}
