<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $table    = 'contact_us';
    protected $fillable = ['user_id','name','phone','message','email'];
    protected $guarded  = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        $this->update(['showOrNow' => 1]);
    }
}
