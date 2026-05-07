<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opponent extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'country_key',
        'mobile',
        'email',
        'location',
        'nationality_id',
    ];
}
