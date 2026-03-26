<?php

namespace App\Models\Qestass;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = ['name','city','branch','swift_code'];
}
