<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['rate','order_id','store_id','seconduser_id','showOrhide' , 'comment','user_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
