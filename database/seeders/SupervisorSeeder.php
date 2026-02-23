<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SupervisorSeeder extends Seeder
{
    public function run()
    {
        // Get the manager first (should exist from ManagerSeeder)
        $manager = User::where('email', 'manager@example.com')->first();

        // SUPERVISOR
        $supervisorEmail = 'supervisor@example.com';
        $supervisor = User::where('email', $supervisorEmail)->first();
        
        if (!$supervisor) {
            $supervisor = User::create([
                'name' => 'Supervisor',
                'email' => $supervisorEmail,
                'password' => bcrypt('password123'),
                'is_admin' => false,
                'is_supervisor' => true,
                'role' => 'supervisor',
                'department' => 'IT',
                'manager_id' => $manager ? $manager->id : null,
                'status' => 'approved',
            ]);
        }

        // Update employee to be assigned to this supervisor
        $employee = User::where('email', 'employee@example.com')->first();
        
        if ($employee) {
            $employee->update([
                'supervisor_id' => $supervisor->id,
            ]);
        }
    }
}
