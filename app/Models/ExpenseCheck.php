<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCheck extends Model
{
    protected $fillable = [
        'check_number',
        'bank_name',
        'status_id',
        'clearance_date',
        'deposit_account',
        'expense_id'
    ];
}
