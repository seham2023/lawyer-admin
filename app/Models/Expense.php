<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'category_id',
        'status_id',
        'file_path',
        'name',
        'receipt_number',
        'description',
        'user_id',
        'matter_id',
        'date',
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

    public function check()
    {
        return $this->hasOne(ExpenseCheck::class);
    }
}
