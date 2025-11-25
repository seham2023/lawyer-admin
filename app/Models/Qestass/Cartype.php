<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Cartype extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['name'];

    public function joinRequests()
    {
        return $this->hasMany(DelegateJoinrequest::class);
    }
    
}
