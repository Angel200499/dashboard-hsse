<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managers = [
            [
                'name' => 'Manager HSSE',
                'username' => 'mgr_hsse',
                'password' => Hash::make('password123'),
                'role' => 'Manager HSSE',
                'fungsi' => 'HSSE',
                'is_active' => true,
            ],
            [
                'name' => 'Manager Operation',
                'username' => 'mgr_operation',
                'password' => Hash::make('password123'),
                'role' => 'Manager Function',
                'fungsi' => 'Operation',
                'is_active' => true,
            ],
            [
                'name' => 'Manager Maintenance',
                'username' => 'mgr_maintenance',
                'password' => Hash::make('password123'),
                'role' => 'Manager Function',
                'fungsi' => 'Maintenance',
                'is_active' => true,
            ],
            [
                'name' => 'Manager Business Support',
                'username' => 'mgr_business_support',
                'password' => Hash::make('password123'),
                'role' => 'Manager Function',
                'fungsi' => 'Business Support',
                'is_active' => true,
            ],
        ];

        foreach ($managers as $manager) {
            User::updateOrCreate(
                ['username' => $manager['username']],
                $manager
            );
        }
    }
}
