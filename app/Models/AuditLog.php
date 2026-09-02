<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;
    
    /**
     * The "booted" method of the model.
     * Established to ensure log immutability.
     */
    protected static function booted()
    {
        static::updating(function ($log) {
            return false;
        });

        static::deleting(function ($log) {
            return false;
        });
    }

    protected $fillable = [
        'admin_user_id',
        'target_user_id',
        'action_type',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
