<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Properity extends Model
{
    use HasFactory, HasTranslations;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name' , 'feature_id'];
    public $translatable = ['name'];
    
    public function feature(){
        return $this->belongsTo(Feature::class);
    }

    public function productfeatureproperities(){
        return $this->hasMany(Productfeatureproperity::class);
    }
}
