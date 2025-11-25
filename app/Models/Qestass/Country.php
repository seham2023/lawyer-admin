<?php

namespace App\Models;

use App\Traits\Uploadable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Spatie\Translatable\HasTranslations;

class Country extends Model
{
    const EGYPT_ID = 1;
    const SA_ID = 2;

    use HasFactory, HasTranslations, Uploadable;

    protected $fillable = ['name', 'currency', 'currency_code', 'iso2', 'iso3', 'calling_code', 'flag', 'active','example'];

    public $translatable = ['name', 'currency', 'currency_code'];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    public function setActiveAttribute()
    {
        $this->attributes['active'] = \Request::has('active');
    }

    public function setFlagAttribute($value)
    {
        if (null != $value && is_file($value)) {
            $this->attributes['flag'] = $this->uploadFile($value, 'flags', true, 23, 17);
        }else{
            $this->attributes['flag'] = $value ;
        }
    }

    public function getFlagPathAttribute()
    {
        return asset('assets/uploads/flags/'  . $this->flag);
    }

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($instance) {
            File::delete(public_path('assets/uploads/flags/' . $instance->flag));
        });
    }
}
