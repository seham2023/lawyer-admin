<?php

namespace App\Models;

use App\Models\PaymentDetail;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'amount',
        'tax',
        'currency_id',
    ];

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class);
    }

    public function getRemainingPaymentAttribute()
    {
        // Total amount paid via paymentDetails
        $paidAmount = $this->paymentDetails()->sum('amount');

        // Remaining amount is the payment's total amount minus the paid amount
        return $this->amount - $paidAmount;
    }

    public function getTotalPaidAttribute()
    {
        return $this->paymentDetails()->sum('amount');
    }
}
