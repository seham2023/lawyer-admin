<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class StoreMenuCategory extends Model
{
    use HasFactory, HasTranslations;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name' , 'store_id'];
    public $translatable = ['name'];

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function store(){
        return $this->belongsTo(Store::class);
    }
}
