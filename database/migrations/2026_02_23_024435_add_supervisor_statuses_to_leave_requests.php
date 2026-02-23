<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add supervisor statuses to the enum
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status ENUM('pending', 'pending_supervisor', 'pending_manager', 'supervisor_approved_pending_manager', 'pending_admin', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous enum values
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status ENUM('pending', 'pending_manager', 'pending_admin', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");
    }
};
