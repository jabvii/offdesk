<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // 'user_approved', 'user_rejected', 'user_deleted', 'leave_approved', 'leave_rejected'
            $table->foreignId('performed_by')->constrained('users')->cascadeOnDelete(); // Technical admin who performed the action
            $table->morphs('auditable'); // Polymorphic relation to User or LeaveRequest
            $table->json('changes')->nullable(); // JSON of what changed
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('action');
            $table->index('performed_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
