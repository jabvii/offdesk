<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{ 
    public function up(): void
    {
        Schema::create('leave_request_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->onDelete('cascade');
            $table->date('date');
            $table->enum('session', ['whole_day', 'morning', 'afternoon'])->default('whole_day');
            $table->timestamps();
            
            // Unique constraint: one session per day per leave request
            $table->unique(['leave_request_id', 'date']);
        }); 
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_sessions');
    }
};
