<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    protected $fillable = [
        'payment_id',
        'name',
        'payment_type',
        'amount',
        'paid_at',
        'pay_method_id',
        'details',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the payment that owns this detail.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the payment method used for this partial payment.
     */
    public function payMethod()
    {
        return $this->belongsTo(PayMethod::class);
    }
}
