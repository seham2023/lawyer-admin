<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductAdditive extends Model
{
    use HasFactory;

    protected $fillable = ['product_additive_id','order_product_id','price','qty'];

    public function productadditive(){
        return $this->belongsTo(ProductAdditive::class,'product_additive_id');
    }
}
