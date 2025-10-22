<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSession extends Model
{
    protected $fillable = [
        'session_id',
        'payment_id',
        'provider',
        'status',
        'amount',
        'currency',
        'buyer_phone',
        'order_reference_id',
        'merchant_code',
        'web_url',
        'response_data',
        'case_record_id',
    ];

    protected $casts = [
        'response_data' => 'array',
    ];

    public function caseRecord(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class);
    }
}
