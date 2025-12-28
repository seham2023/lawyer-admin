<?php

namespace App\Models\Qestass;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interval extends Model
{
    use HasFactory;

    protected $connection = 'qestass_app';

    protected $fillable = ['from', 'to', 'time_id', 'shift_id', 'user_id'];

    public function time()
    {
        return $this->belongsTo(Time::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
