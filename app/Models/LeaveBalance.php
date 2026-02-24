<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'user_id',
        'leave_type_id',
        'total_credits',
        'used_credits',
        'pending_credits',
        'year'
    ];

    protected $casts = [
        'total_credits' => 'float',
        'used_credits' => 'float',
        'pending_credits' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function getAvailableCreditsAttribute(): float
    {
        return $this->total_credits - $this->used_credits - $this->pending_credits;
    }
}