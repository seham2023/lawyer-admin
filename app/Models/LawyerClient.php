<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LawyerClient extends Model
{
    protected $fillable = [
        'lawyer_id',
        'client_id',
    ];
}
