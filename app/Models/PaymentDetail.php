<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    protected $fillable = [
        'name', 'payment_type', 'amount', 'datetime', 'details','payment_id','case_record_id',
    ];
}
