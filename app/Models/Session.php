<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{

    protected $table = 'case_sessions';
    protected $fillable = [
        'case_number',
        'title',
        'details',
        'datetime',
        'priority',
        'case_record_id',
        'client_id'
    ];
}
