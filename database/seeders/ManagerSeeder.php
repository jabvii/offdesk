<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveBalance;

class ManagerSeeder extends Seeder
{
    public function run()
    {
        $currentYear = date('Y');
        $departments = ['IT', 'Accounting', 'HR', 'Treasury', 'Sales', 'Planning', 'Visual', 'Engineering'];

        foreach ($departments as $index => $dept) {
            $managerEmail = 'manager' . ($index + 1) . '@example.com';

            if (!User::where('email', $managerEmail)->exists()) {
                $manager = User::create([
                    'name' => ucfirst($dept) . ' Manager',
                    'email' => $managerEmail,
                    'password' => bcrypt('password123'),
                    'role' => 'manager',
                    'status' => 'approved',
                    'department' => $dept,
                    'is_admin' => false,
                ]);

                // Create leave balances for manager
                $leaveTypes = LeaveType::all();
                foreach ($leaveTypes as $type) {
                    LeaveBalance::create([
                        'user_id' => $manager->id,
                        'leave_type_id' => $type->id,
                        'total_credits' => $type->max_days,
                        'used_credits' => 0,
                        'pending_credits' => 0,
                        'year' => $currentYear,
                    ]);
                }
            }
        }
    }
}
