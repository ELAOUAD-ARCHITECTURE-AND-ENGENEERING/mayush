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
        'description',
        'ip_address',
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
