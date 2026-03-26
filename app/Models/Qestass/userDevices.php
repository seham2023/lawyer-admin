<?php

namespace App\Models\Qestass;

use Illuminate\Database\Eloquent\Model;

class userDevices extends Model
{
    protected $table = "devices";

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
