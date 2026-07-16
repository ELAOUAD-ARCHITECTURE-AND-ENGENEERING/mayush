<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_conversation_id',
        'sender_type',
        'sender_id',
        'message',
    ];

    public function conversation()
    {
        return $this->belongsTo(SupportConversation::class, 'support_conversation_id');
    }

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
