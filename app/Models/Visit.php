<?php

namespace App\Models;

use App\Models\Qestass\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'visit_date',
        'purpose',
        'notes'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the primary payment for this visit.
     */
    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    /**
     * Get all payments for this visit.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
