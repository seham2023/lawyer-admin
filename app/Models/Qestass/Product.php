<?php

namespace App\Models;

use Session;
use DateTime;
use App\Traits\Uploadable;
use App\Traits\GeneralTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, HasTranslations, GeneralTrait, Uploadable ;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','image','desc','type','available','store_menu_category_id','store_id'];

    public $translatable = ['name','desc'];

    public function store(){
        return $this->belongsTo(Store::class);
    }


    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
     
    public function storeMenuCategory(){
        return $this->belongsTo(MenuCategory::class);
    }

    public function productfeatures(){
        return $this->hasMany(Productfeature::class);
    }

    public function groups(){
        return $this->hasMany(ProductGroup::class);
    }

    public function groupOne(){
        return $this->groups()->where('properities' , null)->first();
    }


    public function qty(){
        if($this->type=='simple'){
            $qty = $this->groups()->first()->in_stock_qty;
        }else{
            $qty = $this->groups()->where('properities','!=',NULL)->sum('in_stock_qty');
        }
        return $qty;
    }

    public function getImagePathAttribute(){
        $image = $this->image == null ? 'image.png' : $this->image;
        return asset('assets/uploads/products/' . $image);
    }

    public function setImageAttribute($value) {
        if (is_file($value)) {
            $this->attributes['image'] = $this->uploadFile($value, 'products');
        }
    }

    public static function boot() {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['image'], 'products');

        });

    }

    public function display_price(){
        $lang = app()->getLocale();

        if($this->type == 'simple'){
            $sar = trans('stores.sar');
            $group = $this->groups()->first();
            $hasDiscount = 0;
            if($group && $group->discount_price != NULL  && $group->discount_price > 0){

                $paymentDate    = date('Y-m-d');
                $paymentDate    = date('Y-m-d', strtotime($paymentDate));

                if($group->from == NULL){
                    $contractDateBegin = date('Y-m-d');
                }else{
                    $contractDateBegin = date('Y-m-d', strtotime($group->from));
                }

                if($group->to == NULL){
                    $datetime = new DateTime('tomorrow');
                    $contractDateEnd =  $datetime->format('Y-m-d');
                }else{
                    $contractDateEnd = date('Y-m-d', strtotime($group->to));
                }

                if (($paymentDate >= $contractDateBegin) && ($paymentDate <= $contractDateEnd)){
                    $price =  $group->discount_price;
                    $hasDiscount = 1;
                }
            }
            if($hasDiscount == 1){
//                if($lang == 'ar'){
//                    $price = $this->convert2arabic($price);
//                    $group->price = $this->convert2arabic($group->price);
//                }
                return '<span style="color:#EC2F2F">'.$price.' '.$sar.'    </span><del style"color:#989898;"><small>'.$group->price.' '.$sar.'</small></del>';
            }else{
//                if($lang == 'ar'){
//                    $group->price = $this->convert2arabic($group->price);
//                }
                return '<span style="color:#3DB9B4">'.$group?->price.' '.$sar.'</span>';
            }
        }else{
            return '<span style="color:#3DB9B4">'.trans('stores.price_according_to_choice').'</span>';
        }
    }

    public function price(){
        $lang = app()->getLocale();

        if($this->type == 'simple'){
            $sar = trans('stores.sar');
            $group = $this->groups()->first();
            $hasDiscount = 0;
            if($group && $group->discount_price != NULL  && $group->discount_price > 0){

                $paymentDate    = date('Y-m-d');
                $paymentDate    = date('Y-m-d', strtotime($paymentDate));

                if($group->from == NULL){
                    $contractDateBegin = date('Y-m-d');
                }else{
                    $contractDateBegin = date('Y-m-d', strtotime($group->from));
                }

                if($group->to == NULL){
                    $datetime = new DateTime('tomorrow');
                    $contractDateEnd =  $datetime->format('Y-m-d');
                }else{
                    $contractDateEnd = date('Y-m-d', strtotime($group->to));
                }

                if (($paymentDate >= $contractDateBegin) && ($paymentDate <= $contractDateEnd)){
                    $price =  $group->discount_price;
                    $hasDiscount = 1;
                }
            }
            if($hasDiscount == 1){
                if($lang == 'ar'){
                    $price = $this->convert2arabic($price);
                    $group->price = $this->convert2arabic($group->price);
                }
                return $price." ".$sar;
            }else{
                if($lang == 'ar'){
                    if($group){
                      $group->price = $this->convert2arabic($group->price);
                       return $group->price.'  ' ;
                    }
                }else{
                    if($group){
                        return $group->price.' '.$sar;
                    }
                }

            }
        }else{
            return trans('stores.price_according_to_choice');
        }
    }

    public function main_price(){
        $lang = app()->getLocale();
        $price=$this->groups()->first()?->price;
        if($lang == 'ar'){
                $price = $this->convert2arabic( $price);
        }
        return $price;

    }

    public function additive()
    {
        return $this->hasMany(ProductAdditive::class, 'product_id');
    }





}
