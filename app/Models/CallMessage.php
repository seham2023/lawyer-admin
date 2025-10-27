<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallMessage extends Model
{
    protected $table = 'call_messages';

    protected $fillable = [
        'call_id',
        'sender_id',
        'message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the video call this message belongs to
     */
    public function videoCall(): BelongsTo
    {
        return $this->belongsTo(VideoCall::class, 'call_id');
    }

    /**
     * Get the sender of this message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Scope: Get messages for a specific call
     */
    public function scopeForCall($query, $callId)
    {
        return $query->where('call_id', $callId);
    }

    /**
     * Scope: Get messages from a specific user
     */
    public function scopeFromUser($query, $userId)
    {
        return $query->where('sender_id', $userId);
    }

    /**
     * Scope: Get recent messages
     */
    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}

