<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class District extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['name', 'center', 'region_id', 'city_id'];

    public $translatable = ['name'];

    protected $casts = [
        'boundaries' => 'json',
        'center' => 'json',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

}
