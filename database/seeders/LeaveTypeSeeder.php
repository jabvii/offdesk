<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Vacation Leave',
                'code' => 'vacation',
                'max_days' => 20,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'sick',
                'max_days' => 15,
            ],
            [
                'name' => 'Emergency Leave',
                'code' => 'emergency',
                'max_days' => 4,
            ],
            [
                'name' => 'Paternity Leave',
                'code' => 'paternity',
                'max_days' => 7,
            ],
            [ 
                'name' => 'Parental Leave',
                'code' => 'parental',
                'max_days' => 30,
            ],
            [
                'name' => 'Service Incentive Leave',
                'code' => 'service_incentive',
                'max_days' => 5,
            ],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::updateOrCreate(
                ['code' => $type['code']], // unique key
                $type
            );
        }
    }
}