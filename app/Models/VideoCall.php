<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoCall extends Model
{
    protected $fillable = [
        'caller_id',
        'receiver_id',
        'case_record_id',
        'session_id',
        'token',
        'api_key',
        'status',
        'call_type',
        'started_at',
        'answered_at',
        'ended_at',
        'duration',
        'answered_on_web',
        'answered_on_mobile',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'answered_at' => 'datetime',
        'ended_at' => 'datetime',
        'answered_on_web' => 'boolean',
        'answered_on_mobile' => 'boolean',
    ];

    /**
     * Get the caller user
     */
    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    /**
     * Get the receiver user
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the associated case record
     */
    public function caseRecord(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class);
    }

    /**
     * Scope to get active calls
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get pending calls
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get calls for a specific user (as caller or receiver)
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('caller_id', $userId)->orWhere('receiver_id', $userId);
    }

    /**
     * Calculate call duration
     */
    public function getCallDurationAttribute()
    {
        if ($this->started_at && $this->ended_at) {
            return $this->ended_at->diffInSeconds($this->started_at);
        }
        return null;
    }

    /**
     * Check if call was answered
     */
    public function wasAnswered(): bool
    {
        return $this->answered_at !== null;
    }

    /**
     * Check if call is still pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if call is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

