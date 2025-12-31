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
        'file_path',
        'name',
        'receipt_number',
        'reason',
        'date_time',
        'check_number',
        'bank_name',
        'clearance_date',
        'deposit_account',
        'user_id',
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

    /**
     * Get the primary payment for this expense.
     */
    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    /**
     * Get all payments for this expense.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    // Relationship with Client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function check()
    {
        return $this->hasOne(ExpenseCheck::class);
    }
}
