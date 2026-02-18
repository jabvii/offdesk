<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestSession extends Model
{
    protected $fillable = [
        'leave_request_id',
        'date',
        'session',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    /**
     * Get the day value (1 for whole_day, 0.5 for morning/afternoon)
     */
    public function getDayValue(): float
    {
        return $this->session === 'whole_day' ? 1 : 0.5;
    }
}
