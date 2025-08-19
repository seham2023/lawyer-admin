<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'address',
        'street',
        'city_id',
        'state_id',
        'country_id',
    ];

    // Relationship with City
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // Relationship with State
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    // Relationship with Country
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
