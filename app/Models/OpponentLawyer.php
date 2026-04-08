<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpponentLawyer extends Model
{
    protected $fillable = [
        'name',
        'country_key',
        'mobile',
        'email',
    ];

}
