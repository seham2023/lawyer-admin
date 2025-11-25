<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use File;
use App\Traits\Uploadable;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory;

class DelegateJoinrequest extends Model
{
    protected $guarded = [];

    use HasFactory,Uploadable;

    public function setDrivingLicenseAttribute($value)
    {
        if ($value && is_file($value))
        {
            $this->attributes['driving_license'] = $this->uploadFile($value, 'users', true, 250, null);
        }

    }

    public function setIdentityCardImageAttribute($value)
    {
        if ($value && is_file($value))
        {
            $this->attributes['identity_card_image'] = $this->uploadFile($value, 'users', true, 250, null);
        }

    }


    public function scopeSearch($query, $searchArray = [])
    {
        $query->where(function ($query) use ($searchArray) {
            if ($searchArray) {
                foreach ($searchArray as $key => $value) {
                    if (str_contains($key, '_id')) {
                        if (null != $value) {
                            $query->Where($key, $value);
                        }
                    } elseif ('order' == $key) {
                    } elseif ('created_at_min' == $key) {
                        if (null != $value) {
                            $query->WhereDate('created_at', '>=', $value);
                        }
                    } elseif ('created_at_max' == $key) {
                        if (null != $value) {
                            $query->WhereDate('created_at', '<=', $value);
                        }
                    } else {
                        $query->Where($key, 'like', '%' . $value . '%');
                    }
                }
            }
        });
        return $query->orderBy('created_at', request()->searchArray && request()->searchArray['order'] ? request()->searchArray['order'] : 'DESC');
    }


    public function nationality()
    {
        return $this->belongsTo(Nationality::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function carType()
    {
        return $this->belongsTo(Cartype::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setPersonalImageAttribute($value)
    {
        if ($value && is_file($value) )
        {
            File::delete(public_path('assets/uploads/users/' . $this->personal_image));
            $this->attributes['personal_image'] = $this->uploadFile($value, 'users', true, 250, null);
        }

    }

    public function setCarBackAttribute($value)
    {
        if ( is_file($value))
        {
            File::delete(public_path('assets/uploads/users/' . $this->car_back));
            $this->attributes['car_back'] = $this->uploadFile($value, 'users', true, 250, null);
        }

    }


    public function setCarFrontAttribute($value)
    {
        if ($value && is_file($value))
        {
            File::delete(public_path('assets/uploads/users/' . $this->car_front));
            $this->attributes['car_front'] = $this->uploadFile($value, 'users', true, 250, null);
        }

    }
}
