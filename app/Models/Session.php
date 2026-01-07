<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $table = 'case_sessions';

    protected $fillable = [
        'case_number',
        'title',
        'details',
        'datetime',
        'priority',
        'case_record_id',
        'judge_name',
        'decision',
        'next_session_date',
        'court_id',
        'user_id'
    ];

    protected $casts = [
        'datetime' => 'datetime',
        'next_session_date' => 'date',
    ];

    /**
     * Get the case record that owns the session.
     */
    public function caseRecord(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class);
    }

    /**
     * Get the court where the session takes place.
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * Get the user who created the session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Qestass\User::class, 'user_id');
    }
}
