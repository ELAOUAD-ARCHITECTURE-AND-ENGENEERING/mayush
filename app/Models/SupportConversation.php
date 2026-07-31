<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_token',
        'user_id',
        'assigned_agent_id',
        'status',
        'conversation_state',
        'active_case_id',
        'current_step',
        'attempt_number',
        'bot_turn_count',
        'bot_enabled',
        'frustration_score',
        'escalation_reason',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function messages()
    {
        return $this->hasMany(SupportMessage::class);
    }
}
