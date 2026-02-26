<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing users
        $employee = User::where('email', 'employee@example.com')->first();
        $manager = User::where('email', 'manager@example.com')->first();
        $supervisor = User::where('email', 'supervisor@example.com')->first();

        if (!$employee || !$manager || !$supervisor) {
            $this->command->warn('Employee, Manager, or Supervisor not found. Please run previous seeders first.');
            return;
        }

        // Get leave types
        $leaveTypes = LeaveType::all();

        if ($leaveTypes->isEmpty()) {
            $this->command->warn('No leave types found. Please run LeaveTypeSeeder first.');
            return;
        }

        // Create multiple leave requests with different statuses

        // 1. Pending Supervisor Review
        LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveTypes->firstWhere('code', 'vacation')->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(7),
            'total_days' => 3,
            'reason' => 'Summer vacation to relax and spend time with family.',
            'status' => 'pending_supervisor',
            'supervisor_id' => $supervisor->id,
        ]);

        // 2. Supervisor Approved, Pending Manager
        LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveTypes->firstWhere('code', 'sick')->id,
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(11),
            'total_days' => 2,
            'reason' => 'Medical appointment and recovery.',
            'status' => 'supervisor_approved_pending_manager',
            'supervisor_id' => $supervisor->id,
            'supervisor_remarks' => 'Approved. Employee seems healthy but needs rest.',
            'supervisor_approved_at' => Carbon::now()->subDays(2),
            'manager_id' => $manager->id,
        ]);

        // 3. Approved by Both, Pending Admin
        LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveTypes->firstWhere('code', 'vacation')->id,
            'start_date' => Carbon::now()->addDays(20),
            'end_date' => Carbon::now()->addDays(24),
            'total_days' => 5,
            'reason' => 'Beach trip for summer break and family bonding.',
            'status' => 'pending_admin',
            'supervisor_id' => $supervisor->id,
            'supervisor_remarks' => 'Approved. Work is covered.',
            'supervisor_approved_at' => Carbon::now()->subDays(5),
            'manager_id' => $manager->id,
            'manager_remarks' => 'Approved. Team can handle workload.',
            'forwarded_at' => Carbon::now()->subDays(3),
        ]);

        // 4. Fully Approved
        LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveTypes->firstWhere('code', 'emergency')->id,
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->subDays(9),
            'total_days' => 2,
            'reason' => 'Family emergency resolved.',
            'status' => 'approved',
            'supervisor_id' => $supervisor->id,
            'supervisor_remarks' => 'Urgent situation, approved.',
            'supervisor_approved_at' => Carbon::now()->subDays(12),
            'manager_id' => $manager->id,
            'manager_remarks' => 'Emergency case, approved.',
            'forwarded_at' => Carbon::now()->subDays(11),
            'admin_remarks' => 'Approved and processed.',
        ]);

        // 5. Rejected by Supervisor
        LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveTypes->firstWhere('code', 'vacation')->id,
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()->subDays(25),
            'total_days' => 6,
            'reason' => 'International trip for vacation.',
            'status' => 'rejected',
            'supervisor_id' => $supervisor->id,
            'supervisor_remarks' => 'Rejected - Multiple leave requests in same period.',
            'supervisor_approved_at' => Carbon::now()->subDays(32),
        ]);

        // 6. Rejected by Manager
        LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveTypes->firstWhere('code', 'sick')->id,
            'start_date' => Carbon::now()->subDays(20),
            'end_date' => Carbon::now()->subDays(18),
            'total_days' => 3,
            'reason' => 'Health issues.',
            'status' => 'rejected',
            'supervisor_id' => $supervisor->id,
            'supervisor_remarks' => 'Approved.',
            'supervisor_approved_at' => Carbon::now()->subDays(22),
            'manager_id' => $manager->id,
            'manager_remarks' => 'Rejected - Critical project deadline cannot be missed.',
            'forwarded_at' => Carbon::now()->subDays(21),
        ]);

        // 7. Pending Manager Review
        LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveTypes->firstWhere('code', 'paternity')->id,
            'start_date' => Carbon::now()->addDays(30),
            'end_date' => Carbon::now()->addDays(36),
            'total_days' => 7,
            'reason' => 'Paternity leave for new baby.',
            'status' => 'pending_manager',
            'supervisor_id' => $supervisor->id,
            'supervisor_remarks' => 'Approved. Congratulations on the new baby!',
            'supervisor_approved_at' => Carbon::now()->subDays(1),
            'manager_id' => $manager->id,
        ]);

        // 8. Additional Vacation - Pending Supervisor
        LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveTypes->firstWhere('code', 'vacation')->id,
            'start_date' => Carbon::now()->addDays(45),
            'end_date' => Carbon::now()->addDays(49),
            'total_days' => 5,
            'reason' => 'Extended weekend trip.',
            'status' => 'pending_supervisor',
            'supervisor_id' => $supervisor->id,
        ]);

        // 9. Service Incentive Leave - Approved
        LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveTypes->firstWhere('code', 'service_incentive')->id,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->subDays(2),
            'total_days' => 4,
            'reason' => 'Annual service incentive leave.',
            'status' => 'approved',
            'supervisor_id' => $supervisor->id,
            'supervisor_remarks' => 'Approved.',
            'supervisor_approved_at' => Carbon::now()->subDays(8),
            'manager_id' => $manager->id,
            'manager_remarks' => 'Approved.',
            'forwarded_at' => Carbon::now()->subDays(7),
            'admin_remarks' => 'Processed and approved.',
        ]);

        // 10. Parental Leave - Pending Admin Review
        LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveTypes->firstWhere('code', 'parental')->id,
            'start_date' => Carbon::now()->addDays(60),
            'end_date' => Carbon::now()->addDays(89),
            'total_days' => 30,
            'reason' => 'Long term parental leave for childcare.',
            'status' => 'pending_admin',
            'supervisor_id' => $supervisor->id,
            'supervisor_remarks' => 'Approved - Temporary coverage arranged.',
            'supervisor_approved_at' => Carbon::now()->subDays(10),
            'manager_id' => $manager->id,
            'manager_remarks' => 'Approved - Team restructured for coverage.',
            'forwarded_at' => Carbon::now()->subDays(8),
        ]);

        $this->command->info('LeaveRequest seeder completed successfully!');
    }
}
