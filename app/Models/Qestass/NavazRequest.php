<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NavazRequest extends Model
{
    use HasFactory;
    
    protected $fillable = ['user_id' , 'random' , 'status' ,'national_id' , 'request_id' , 'code_expire' , 'trans_id' , 'service']; 
    
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
