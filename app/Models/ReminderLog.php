<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // We don't need updated_at for logs

    protected $fillable = [
        'reminder_id',
        'channel',
        'status',
        'response',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Get the reminder that owns the log.
     */
    public function reminder(): BelongsTo
    {
        return $this->belongsTo(Reminder::class);
    }

    /**
     * Scope a query to only include successful logs.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include failed logs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include logs for a specific channel.
     */
    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Create a success log entry.
     */
    public static function logSuccess(int $reminderId, string $channel, ?string $response = null): self
    {
        return static::create([
            'reminder_id' => $reminderId,
            'channel' => $channel,
            'status' => 'success',
            'response' => $response,
            'sent_at' => now(),
        ]);
    }

    /**
     * Create a failure log entry.
     */
    public static function logFailure(int $reminderId, string $channel, string $errorMessage, ?string $response = null): self
    {
        return static::create([
            'reminder_id' => $reminderId,
            'channel' => $channel,
            'status' => 'failed',
            'error_message' => $errorMessage,
            'response' => $response,
            'sent_at' => now(),
        ]);
    }
}
