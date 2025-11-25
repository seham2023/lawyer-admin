<?php

namespace App\Models;

use App\Traits\Uploadable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStep extends Model
{
    use HasFactory  ,Uploadable ;
    protected $fillable = ['image','title_ar','desc_ar','title_en','desc_en'];

    public function getImagePathAttribute()
    {
        return asset('assets/uploads/settings/' . $this->image);
    }

    public function setImageAttribute($value) {
        if (is_file($value)) {
            $this->attributes['image'] = $this->uploadOne($value, 'settings');
        }
    }
}
