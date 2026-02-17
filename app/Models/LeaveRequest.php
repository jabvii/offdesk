<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'start_session',
        'end_session',
        'total_days',
        'reason',
        'status',
        'admin_remarks',
        'admin_decision',
        'manager_id',
        'manager_remarks',
        'forwarded_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    // Helper methods
    public function isPendingManagerReview(): bool
    {
        return $this->status === 'pending_manager';
    }

    public function isPendingAdminReview(): bool
    {
        return $this->status === 'pending_admin';
    }

    public function isFinal(): bool
    {
        return in_array($this->status, ['approved', 'rejected']);
    }

    public function isEmployeeRequest(): bool
    {
        return $this->user->isEmployee();
    }

    public function isManagerRequest(): bool
    {
        return $this->user->isManager();
    }

    // Scopes
    public function scopePendingForManager($query, $managerId)
    {
        return $query->where('status', 'pending_manager')
                     ->whereIn('user_id', User::where('manager_id', $managerId)->pluck('id'));
    }

    public function scopePendingForAdmin($query)
    {
        return $query->where('status', 'pending_admin');
    }

    public function scopeFinal($query)
    {
        return $query->whereIn('status', ['approved', 'rejected']);
    }

    public static function calculateBusinessDays($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = 0;

        while ($start->lte($end)) {
            if ($start->isWeekday()) {
                $days++;
            }
            $start->addDay();
        }

        return $days;
    }
}