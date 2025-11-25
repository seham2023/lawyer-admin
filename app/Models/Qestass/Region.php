<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Region extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['name', 'boundaries', 'country_id', 'center', 'code', 'population'];

    public $translatable = ['name'];

    protected $casts = [
        'boundaries' => 'json',
        'center' => 'json',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
