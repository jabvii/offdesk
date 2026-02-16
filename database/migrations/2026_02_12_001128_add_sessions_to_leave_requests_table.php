<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->enum('start_session', ['full', 'morning', 'afternoon'])
                ->default('full')
                ->after('end_date');

            $table->enum('end_session', ['full', 'morning', 'afternoon'])
                ->default('full')
                ->after('start_session');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['start_session', 'end_session']);
        });
    }
};