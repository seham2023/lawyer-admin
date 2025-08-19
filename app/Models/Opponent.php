<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opponent extends Model
{
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'location',
        'nationality_id',
    ];
}
