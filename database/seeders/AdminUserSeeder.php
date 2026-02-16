<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $adminEmail = 'admin@example.com';
        if (!User::where('email', $adminEmail)->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => $adminEmail,
                'password' => bcrypt('password123'),
                'is_admin' => true,
                'status' => 'approved',
            ]);
        }
    }
}