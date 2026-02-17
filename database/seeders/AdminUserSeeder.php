<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Create Admin
        $adminEmail = 'admin@example.com';
        if (!User::where('email', $adminEmail)->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => $adminEmail,
                'password' => bcrypt('password123'),
                'is_admin' => true,
                'role' => 'admin',
                'status' => 'approved',
                'department' => null,
            ]);
        }

        // Create Technical Admin
        $technicalEmail = 'technical@example.com';
        if (!User::where('email', $technicalEmail)->exists()) {
            User::create([
                'name' => 'Technical Admin',
                'email' => $technicalEmail,
                'password' => bcrypt('password123'),
                'is_admin' => true,
                'role' => 'technical',
                'status' => 'approved',
                'department' => null,
            ]);
        }
    }
}