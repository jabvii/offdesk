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
        'department',
        'role',
        'manager_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'status' => 'string',
        'role' => 'string',
    ];

    // Relationships
    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id', 'id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // Helper methods for role checking
    public function isManager(): bool
    {
        return $this->role === 'manager';
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
        return in_array($this->role, ['admin', 'technical', 'manager']);
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