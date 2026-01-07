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

    /**
     * Get payment details through the primary payment.
     */
    public function paymentDetails()
    {
        // Get payment details through the visit's payment
        return $this->hasManyThrough(
            \App\Models\PaymentDetail::class,
            Payment::class,
            'payable_id', // Foreign key on payments table
            'payment_id', // Foreign key on payment_details table
            'id', // Local key on visits table
            'id' // Local key on payments table
        )->where('payments.payable_type', Visit::class);
    }
}
