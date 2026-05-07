<?php

namespace App\Models;

use App\Models\Qestass\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Visit extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'user_id',
        'client_id',
        'status_id',
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

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)->using(ServiceVisit::class);
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

    /**
     * Synchronize the associated payment based on selected services.
     */
    public function syncPaymentWithServices(?array $serviceIds = null): void
    {
        if ($serviceIds === null) {
            $amount = $this->services()->sum('price');
        } else {
            $amount = \App\Models\Service::whereIn('id', $serviceIds)->sum('price');
        }

        $payment = $this->payment()->first();

        if ($payment) {
            $payment->update([
                'amount' => $amount,
                'client_id' => $this->client_id,
            ]);
        } elseif ($amount > 0) {
            $this->payment()->create([
                'amount' => $amount,
                'tax' => 0,
                'currency_id' => \App\Support\Money::getCurrencyId(),
                'user_id' => $this->user_id ?? auth()->id(),
                'client_id' => $this->client_id,
                'pay_method_id' => 1, // Default to Cash/Direct
                'status_id' => 1, // Default to Pending/Unpaid
            ]);
        }
    }

    protected static function booted(): void
    {
        static::saved(function (Visit $visit) {
            if ($visit->wasChanged('client_id')) {
                $visit->syncPaymentWithServices();
            }
        });
    }
}
