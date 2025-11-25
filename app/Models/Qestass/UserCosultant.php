<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCosultant extends Model
{
    use HasFactory;
    protected $fillable = ['consultant_id','user_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function consultant(){
        return $this->belongsTo(Consultant::class);
    }
}
