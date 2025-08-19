<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'category_id',
        'status_id',
        'currency_id',
        'pay_method_id',
        'payment_id',
        'file_path',
        'name',
        'receipt_number',
        'reason',
        'date_time',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function payMethod()
    {
        return $this->belongsTo(PayMethod::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
    public function check()
    {
        return $this->hasOne(ExpenseCheck::class);
    }
}
