<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LawyerUser extends Model
{
    protected $table = 'lawyer_users';

    protected $fillable = [
        'lawyer_id',
        'user_id',
        'user_type',
    ];

    public function lawyer()
    {
        return $this->belongsTo(Qestass\User::class, 'lawyer_id');
    }

    public function user()
    {
        return $this->belongsTo(Qestass\User::class, 'user_id');
    }
}
