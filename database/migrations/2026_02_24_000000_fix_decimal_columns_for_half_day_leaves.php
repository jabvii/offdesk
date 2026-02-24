<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix columns to support half-day leaves (0.5 increments)
     */
    public function up(): void
    {
        // Change leave_requests.total_days from integer to decimal
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->decimal('total_days', 5, 1)->change();
        });

        // Change leave_balances credits columns from integer to decimal
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->decimal('total_credits', 5, 1)->change();
            $table->decimal('used_credits', 5, 1)->default(0)->change();
            $table->decimal('pending_credits', 5, 1)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->integer('total_days')->change();
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->integer('total_credits')->change();
            $table->integer('used_credits')->default(0)->change();
            $table->integer('pending_credits')->default(0)->change();
        });
    }
};
