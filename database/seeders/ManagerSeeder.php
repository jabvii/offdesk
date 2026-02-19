<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class ManagerSeeder extends Seeder
{
    public function run()
    {
        // MANAGER
        $managerEmail = 'manager@example.com';
        $manager = User::where('email', $managerEmail)->first();
        
        if (!$manager) {
            $manager = User::create([
                'name' => 'Manager',
                'email' => $managerEmail,
                'password' => bcrypt('password123'),
                'is_admin' => false,
                'role' => 'manager',
                'department' => 'IT',
                'status' => 'approved',
            ]);
        }

        // EMPLOYEE assigned to MANAGER
        $employeeEmail = 'employee@example.com';
        $employee = User::where('email', $employeeEmail)->first();
        
        if ($employee) {
            $employee->update([
                'role' => 'employee',
                'manager_id' => $manager->id,
            ]);
        } else {
            User::create([
                'name' => 'Employee',
                'email' => $employeeEmail,
                'password' => bcrypt('password123'),
                'is_admin' => false,
                'role' => 'employee',
                'department' => 'IT',
                'manager_id' => $manager->id,
                'status' => 'approved',
            ]);
        }
    }
}
