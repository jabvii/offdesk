<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'action',
        'performed_by',
        'auditable_id',
        'auditable_type',
        'changes',
        'remarks',
    ];

    protected $casts = [
        'changes' => 'json',
    ];

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function auditable()
    {
        return $this->morphTo();
    }
}

