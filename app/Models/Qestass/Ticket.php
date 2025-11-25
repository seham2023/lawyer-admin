<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['order_id','subject','text','user_id', 'answer' , 'answer_at' , 'status'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function status(){
        return trans('ticket.'.$this->status);
    }

    public function images(){
        return $this->hasMany(Ticketimage::class);
    }
}
