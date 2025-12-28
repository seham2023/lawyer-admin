<?php

namespace App\Models\Qestass;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Time extends Model
{
    use HasFactory;

    protected $connection = 'qestass_app';

    protected $fillable = ['type', 'day', 'user_id'];

    public function intervals()
    {
        return $this->hasMany(Interval::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
