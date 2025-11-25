<?php

namespace App\Models;

use Session;
use DateTime;
use App\Models\Properity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductGroup extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['properities','price','discount_price','from','to','in_stock_type','in_stock_sku','allow_in_stock_system','in_stock_notification','in_stock_qty','allow_late_orders','allow_purchase_of_one_quantity','require_shipping','enable_the_great_discount','the_great_discount','the_great_discount_min_qty','appearance','status','product_id'];
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['properities_data'];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function getProperitiesDataAttribute(){
        $properities = [];
        foreach (json_decode($this->properities) as $value) {
            $properity = Properity::find($value);

            if($properity){
                array_push($properities,$properity);
            }
        }
        return  $properities ;
    }

    public function price(){
        $price = $this->price;
        if($this->discount_price != NULL && $this->discount_price > 0){

            $paymentDate    = date('Y-m-d');
            $paymentDate    = date('Y-m-d', strtotime($paymentDate));

            if($this->from == NULL){
                $contractDateBegin = date('Y-m-d');
            }else{
                $contractDateBegin = date('Y-m-d', strtotime($this->from));
            }

            if($this->to == NULL){
                $datetime = new DateTime('tomorrow');
                $contractDateEnd =  $datetime->format('Y-m-d');
            }else{
                $contractDateEnd = date('Y-m-d', strtotime($this->to));
            }

            if (($paymentDate >= $contractDateBegin) && ($paymentDate <= $contractDateEnd)){
                $price =  $this->discount_price;
            }
        }
        return $price;
    }

    public function _price_(){
        $price = $this->price;
        $hasDiscount = 0;
        if($this->discount_price != NULL  && $this->discount_price > 0){

            $paymentDate    = date('Y-m-d');
            $paymentDate    = date('Y-m-d', strtotime($paymentDate));

            if($this->from == NULL){
                $contractDateBegin = date('Y-m-d');
            }else{
                $contractDateBegin = date('Y-m-d', strtotime($this->from));
            }

            if($this->to == NULL){
                $datetime = new DateTime('tomorrow');
                $contractDateEnd =  $datetime->format('Y-m-d');
            }else{
                $contractDateEnd = date('Y-m-d', strtotime($this->to));
            }

            if (($paymentDate >= $contractDateBegin) && ($paymentDate <= $contractDateEnd)){
                $price =  $this->discount_price;
                $hasDiscount = 1;
            }
        }
        if($hasDiscount == 1){
            return $this->price;
        }else{
            return 0;
        }
    }

    public function _price(){
        $sar = Session::has('lang')&&Session::get('lang')=='en'?'SAR':'ر.س';
        $price = $this->price;
        $hasDiscount = 0;
        if($this->discount_price != NULL  && $this->discount_price > 0){

            $paymentDate    = date('Y-m-d');
            $paymentDate    = date('Y-m-d', strtotime($paymentDate));

            if($this->from == NULL){
                $contractDateBegin = date('Y-m-d');
            }else{
                $contractDateBegin = date('Y-m-d', strtotime($this->from));
            }

            if($this->to == NULL){
                $datetime = new DateTime('tomorrow');
                $contractDateEnd =  $datetime->format('Y-m-d');
            }else{
                $contractDateEnd = date('Y-m-d', strtotime($this->to));
            }

            if (($paymentDate >= $contractDateBegin) && ($paymentDate <= $contractDateEnd)){
                $price =  $this->discount_price;
                $hasDiscount = 1;
            }
        }
        if($hasDiscount == 1){
            return '<span>'.$price.' '.$sar.'</span>'.'<strike>'.$this->price.' '.$sar.'</strike>';

        }else{
            return $this->price.' '.$sar;
        }
    }

    public function _single_price(){
        $sar = Session::has('lang')&&Session::get('lang')=='en'?'SAR':'ر.س';
        $price = $this->price;
        $hasDiscount = 0;
        if($this->discount_price != NULL  && $this->discount_price > 0){

            $paymentDate = date('Y-m-d');
            $paymentDate=date('Y-m-d', strtotime($paymentDate));

            if($this->from == NULL){
                $contractDateBegin = date('Y-m-d');
            }else{
                $contractDateBegin = date('Y-m-d', strtotime($this->from));
            }

            if($this->to == NULL){
                $datetime =  new DateTime('tomorrow');
                $contractDateEnd =  $datetime->format('Y-m-d');
            }else{
                $contractDateEnd = date('Y-m-d', strtotime($this->to));
            }

            if (($paymentDate >= $contractDateBegin) && ($paymentDate <= $contractDateEnd)){
                $price =  $this->discount_price;
                $hasDiscount = 1;
            }
        }
        if($hasDiscount == 1){
            return '<p class="before">
                                <span class=" value">
                                    <span class=" preReductionPrice">
                                        <span class="value">'.$price.'</span>
                                        <span class="currency null"> '.$sar.' </span>
                                    </span>
                                </span>
                    </p>
                    <p class="after">
                                <span class=" value">
                                    <strike class=" preReductionPrice">
                                        <span class="value">'.$this->price.'</span>
                                        <span class="currency null"> '.$sar.' </span>
                                    </strike>
                                </span>
                    </p>
                    ';

        }else{
            return '
            <p class="before">
                                <span class=" value">
                                    <span class=" preReductionPrice">
                                        <span class="value">'.$this->price.'</span>
                                        <span class="currency null"> '.$sar.' </span>
                                    </span>
                                </span>
                    </p>';
        }
    }
}
