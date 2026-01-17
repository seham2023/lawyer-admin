<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'remindable_type',
        'remindable_id',
        'reminder_type',
        'scheduled_at',
        'sent_at',
        'status',
        'channels',
        'metadata',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'channels' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the reminder.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent remindable model (Session, Event, Payment, etc.).
     */
    public function remindable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the logs for the reminder.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ReminderLog::class);
    }

    /**
     * Scope a query to only include pending reminders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include sent reminders.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope a query to only include failed reminders.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include cancelled reminders.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include reminders scheduled before a given time.
     */
    public function scopeScheduledBefore($query, $datetime)
    {
        return $query->where('scheduled_at', '<=', $datetime);
    }

    /**
     * Scope a query to only include reminders for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include reminders of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('reminder_type', $type);
    }

    /**
     * Mark the reminder as sent.
     */
    public function markAsSent(): bool
    {
        return $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark the reminder as failed.
     */
    public function markAsFailed(): bool
    {
        return $this->update([
            'status' => 'failed',
        ]);
    }

    /**
     * Mark the reminder as cancelled.
     */
    public function markAsCancelled(): bool
    {
        return $this->update([
            'status' => 'cancelled',
        ]);
    }

    /**
     * Check if the reminder is due to be sent.
     */
    public function isDue(): bool
    {
        return $this->status === 'pending' && $this->scheduled_at <= now();
    }

    /**
     * Check if the reminder can be sent.
     */
    public function canBeSent(): bool
    {
        return $this->status === 'pending';
    }
}
