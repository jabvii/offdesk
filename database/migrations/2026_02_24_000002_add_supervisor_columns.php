<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add supervisor support to users and leave_requests
     */
    public function up(): void
    {
        // Add supervisor columns to users table
        if (!Schema::hasColumn('users', 'supervisor_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null')->after('manager_id');
            });
        }

        if (!Schema::hasColumn('users', 'is_supervisor')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_supervisor')->default(false)->after('is_admin');
            });
        }

        // Add supervisor approval columns to leave_requests
        if (!Schema::hasColumn('leave_requests', 'supervisor_id')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null')->after('user_id');
            });
        }

        if (!Schema::hasColumn('leave_requests', 'supervisor_approved_at')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->timestamp('supervisor_approved_at')->nullable()->after('forwarded_at');
            });
        }

        if (!Schema::hasColumn('leave_requests', 'supervisor_remarks')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->text('supervisor_remarks')->nullable()->after('supervisor_approved_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('leave_requests', 'supervisor_remarks')) {
                $table->dropColumn('supervisor_remarks');
            }
            if (Schema::hasColumn('leave_requests', 'supervisor_approved_at')) {
                $table->dropColumn('supervisor_approved_at');        
            }
            if (Schema::hasColumn('leave_requests', 'supervisor_id')) {
                $table->dropForeign(['supervisor_id']);
                $table->dropColumn('supervisor_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_supervisor')) {
                $table->dropColumn('is_supervisor');
            }
            if (Schema::hasColumn('users', 'supervisor_id')) {
                $table->dropForeign(['supervisor_id']);
                $table->dropColumn('supervisor_id');
            }
        });
    }
};
