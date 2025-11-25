<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = ['data','message','errors','transaction_id','user_id' ,'amount'];

}
