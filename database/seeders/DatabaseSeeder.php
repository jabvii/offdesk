<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([LeaveTypeSeeder::class,]);
        $this->call(AdminUserSeeder::class);
        $this->call(ManagerSeeder::class);
        $this->call(SupervisorSeeder::class);
    }
}
