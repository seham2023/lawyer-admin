<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uploadable;
use Illuminate\Support\Facades\File;

class DelegateCompany extends Model
{
    use HasFactory,Uploadable;

    protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function getCommericalPathAttribute()
    {
        return  asset('assets/uploads/users/' . $this->commercial_image);
    }

    public function setCommercialImageAttribute($value)
    {
        if($value != 'default.png')
        {
            if ($this->commercial_image != 'default.png' and $this->commercial_image)
            {
                File::delete(public_path('assets/uploads/users/' . $this->commercial_image));
            }
            $this->attributes['commercial_image'] = $this->uploadFile($value, 'users', true, 250, null);
        }

    }
}
