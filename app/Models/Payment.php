<?php

namespace App\Models;

use App\Models\Status;
use App\Models\Currency;
use App\Models\PayMethod;
use App\Models\CaseRecord;
use App\Models\PaymentDetail;
use App\Models\Qestass\User;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'amount',
        'tax',
        'currency_id',
        'user_id',
        'client_id',
        'payment_date',
        'pay_method_id',
        'status_id',
        'image',
        'payable_type',
        'payable_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    /**
     * Get the owning payable model (CaseRecord, Visit, Expense, etc.)
     */
    public function payable()
    {
        return $this->morphTo();
    }

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

    public function payMethod()
    {
        return $this->belongsTo(PayMethod::class, 'pay_method_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    /**
     * @deprecated Use payable() morphTo relationship instead
     */
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
