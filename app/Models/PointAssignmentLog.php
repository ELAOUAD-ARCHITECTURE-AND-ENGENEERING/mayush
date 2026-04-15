<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointAssignmentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'action_type',
        'affected_products_count',
        'payload_backup'
    ];

    public function admin() {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
