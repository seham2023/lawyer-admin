<?php

namespace App\Models;

use App\Models\PaymentDetail;
use App\Models\Qestass\User;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'amount',
        'tax',
        'currency_id',
        'user_id',
        'client_id',
        'payment_date',
        'case_record_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function caseRecord()
    {
        return $this->belongsTo(CaseRecord::class, 'case_record_id');
    }

    public function getRemainingPaymentAttribute()
    {
        // Total amount paid via paymentDetails
        $paidAmount = $this->paymentDetails()->sum('amount');

        // Remaining amount is the payment's total amount minus the paid amount, ensuring it doesn't go negative
        return max(0, $this->amount - $paidAmount);
    }

    public function getTotalPaidAttribute()
    {
        return $this->paymentDetails()->sum('amount');
    }

    public function case()
    {
        return $this->hasOne(CaseRecord::class);
    }
}
