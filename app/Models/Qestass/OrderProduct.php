<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;
    
    protected $fillable = ['order_id','product_id','group_id','price','qty'];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function orderproductadditives(){
        return $this->hasMany(OrderProductAdditive::class);
    }

    public function total_price(){
        $additives = $this->orderproductadditives;
        $additives_price = 0;
        foreach($additives as $key=>$a){
            $additives_price += $a->qty * $a->price;
        }
        //total price
        $price = $this->price * $this->qty;
        $price = $price + $additives_price;
        return number_format($price,2);
    }
    
    public function additives_text(){
        $additives = $this->orderproductadditives;
        $additives_text = '';
        foreach($additives as $key=>$a){
            if($a->productadditive){
                if($key+1 == count($additives)){
                    $additives_text .= $a->productadditive->name.'.';
                }else{
                    $additives_text .= $a->productadditive->name.', ';
                }
            }
        }
        return $additives_text;
    }
}
