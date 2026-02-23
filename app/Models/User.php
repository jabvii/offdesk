<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'admin_remarks',
        'is_admin',
        'is_supervisor',
        'department',
        'role',
        'manager_id',
        'supervisor_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_supervisor' => 'boolean',
        'status' => 'string',
        'role' => 'string',
    ];

    // Relationships
    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id', 'id');
    }

    public function supervisees()
    {
        return $this->hasMany(User::class, 'supervisor_id', 'id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id', 'id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function supervisorApprovals()
    {
        return $this->hasMany(LeaveRequest::class, 'supervisor_id', 'id');
    }

    // Helper methods for role checking
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isSupervisor(): bool
    {
        return $this->is_supervisor === true;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTechnical(): bool
    {
        return $this->role === 'technical';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function canApproveLeaves(): bool
    {
        return in_array($this->role, ['admin', 'technical', 'manager']) || $this->is_supervisor;
    }

    public function canManageUsers(): bool
    {
        return in_array($this->role, ['admin', 'technical']);
    }

    public function canChangeRoles(): bool
    {
        return $this->role === 'technical';
    }
}
