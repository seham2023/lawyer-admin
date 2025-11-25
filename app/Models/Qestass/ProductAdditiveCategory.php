<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ProductAdditiveCategory extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['name'];

    public function store(){
        return $this->belongsTo(Store::class);
    }

    public function productAdditives(){
        return $this->hasMany(ProductAdditive::class);
    }
}
