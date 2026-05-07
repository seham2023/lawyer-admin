<?php

namespace App\Models;

use App\Models\Status;
use App\Models\Currency;
use App\Models\PayMethod;
use App\Models\PaymentDetail;
use App\Models\Qestass\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

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

    protected static function booted(): void
    {
        static::saved(function (Payment $payment): void {
            if ($payment->isPaidStatus()) {
                $payment->markAsPaid();
            }
        });
    }

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

    public function markAsPaid(): void
    {
        $remaining = $this->remaining_payment;

        if ($remaining <= 0) {
            return;
        }

        $this->paymentDetails()->create([
            'name' => 'Marked as paid',
            'payment_type' => 'full',
            'amount' => $remaining,
            'paid_at' => now(),
            'pay_method_id' => $this->pay_method_id,
            'details' => 'Automatically created when payment status was set to Paid.',
        ]);
    }

    public function isPaidStatus(): bool
    {
        if (! $this->status_id) {
            return false;
        }

        $status = $this->relationLoaded('status')
            ? $this->status
            : Status::query()->find($this->status_id);

        if (! $status || $status->type !== 'payment') {
            return false;
        }

        if (in_array(HasTranslations::class, class_uses_recursive($status), true)) {
            return strtolower($status->getTranslation('name', 'en', false)) === 'paid';
        }

        return in_array($status->name, ['Paid', 'مدفوع'], true);
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
}
