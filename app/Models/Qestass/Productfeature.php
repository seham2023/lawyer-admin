<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productfeature extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['product_id' , 'feature_id'];
    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function feature(){
        return $this->belongsTo(Feature::class);
    }

    public function productfeatureproperities(){
        return $this->hasMany(Productfeatureproperity::class);
    }
}
