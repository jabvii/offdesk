<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change the enum to include pending_manager and pending_admin
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'pending_manager', 'pending_admin', 'approved', 'rejected', 'cancelled'])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending')->change();
        });
    }
};
