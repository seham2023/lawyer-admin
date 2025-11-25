<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderView extends Model
{
    use HasFactory;
    protected $fillable = ['order_id','lawyer_id'];

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function lawyer(){
        return $this->belongsTo(User::class);
    }

}
