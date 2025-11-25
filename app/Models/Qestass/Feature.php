<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Feature extends Model
{
    use HasFactory, HasTranslations;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
    public $translatable = ['name'];
    
    public function properities(){
        return $this->hasMany(Properity::class);
    }

    public function productfeatures(){
        return $this->hasMany(Productfeature::class);
    }

}
