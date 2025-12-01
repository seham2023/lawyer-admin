<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Status extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'type', 'user_id'];

    public $translatable = ['name'];

    public function user()
    {
        return $this->belongsTo(\App\Models\Qestass\User::class);
    }


    public function scopeCase($query)
    {
        return $query->where('type', 'case');
    }

    /**
     * Scope a query to only include expense statuses.
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope a query to only include check statuses.
     */
    public function scopeCheck($query)
    {
        return $query->where('type', 'check');
    }
}
