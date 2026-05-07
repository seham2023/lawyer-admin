<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpponentLawyer extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'country_key',
        'mobile',
        'email',
    ];

}
