<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'supervisor_id',
        'supervisor_remarks',
        'supervisor_approved_at',
        'manager_remarks',
        'admin_remarks',
        'manager_id',
        'forwarded_at',
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

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id', 'id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(LeaveRequestSession::class);
    }

    /**
     * Calculate total days based on sessions
     */
    public function calculateTotalDaysFromSessions(): float
    {
        return $this->sessions->sum(function ($session) {
            return $session->getDayValue();
        });
    }

    // Helper methods
    public function isPendingSupervisorReview(): bool
    {
        return $this->status === 'pending_supervisor';
    }

    public function isSupervisorApprovedPendingManager(): bool
    {
        return $this->status === 'supervisor_approved_pending_manager';
    }

    public function isApprovedByBothPendingAdmin(): bool
    {
        return $this->status === 'approved_both_pending_admin';
    }

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

    public function isSupervisorRequest(): bool
    {
        return $this->user->isSupervisor();
    }

    // Scopes
    public function scopePendingForSupervisor($query, $supervisorId)
    {
        return $query->where('status', 'pending_supervisor')
                     ->where('supervisor_id', $supervisorId);
    }

    public function scopePendingForManager($query, $managerId)
    {
        return $query->whereIn('status', ['supervisor_approved_pending_manager', 'pending_manager'])
                     ->where('manager_id', $managerId);
    }

    public function scopePendingForAdmin($query)
    {
        return $query->where('status', 'pending_admin');
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
