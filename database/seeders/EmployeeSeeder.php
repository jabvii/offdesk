<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveBalance;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        $currentYear = date('Y');
        $departments = ['IT', 'Accounting', 'HR', 'Treasury', 'Sales', 'Planning', 'Visual', 'Engineering'];

        // Create 3 employees per department, assigned to their department manager
        foreach ($departments as $deptIndex => $dept) {
            $manager = User::where('role', 'manager')
                ->where('department', $dept)
                ->first();

            for ($i = 1; $i <= 3; $i++) {
                $empNumber = ($deptIndex * 3) + $i;
                $empEmail = 'employee' . $empNumber . '@example.com';

                if (!User::where('email', $empEmail)->exists()) {
                    $employee = User::create([
                        'name' => 'Employee ' . $empNumber . ' - ' . ucfirst($dept),
                        'email' => $empEmail,
                        'password' => bcrypt('password123'),
                        'role' => 'employee',
                        'status' => 'approved',
                        'department' => $dept,
                        'manager_id' => $manager?->id,
                        'is_admin' => false,
                    ]);

                    // Create leave balances for employee
                    $leaveTypes = LeaveType::all();
                    foreach ($leaveTypes as $type) {
                        LeaveBalance::create([
                            'user_id' => $employee->id,
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
}
