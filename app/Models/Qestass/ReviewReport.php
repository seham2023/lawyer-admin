<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewReport extends Model
{

    protected $fillable = ['review_id','reason','user_id'];

    use HasFactory;

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function review(){
        return $this->belongsTo(Review::class);
    }
}
