<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasTranslations;

    protected $fillable = ['name','state_id'];

    public $translatable = ['name'];


    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
