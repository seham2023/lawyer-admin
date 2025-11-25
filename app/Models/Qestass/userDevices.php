<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class userDevices extends Model
{
    protected $table = "devices";

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
