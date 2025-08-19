<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class State extends Model
{
    use HasTranslations;

    protected $fillable = ['name','country_id'];

    public $translatable = ['name'];


    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
