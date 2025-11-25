<?php

namespace App\Models;

use App\Traits\Uploadable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;
use App\Models\Setting;
use App\Http\Resources\StoreResource;
use DateTime;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, HasTranslations;
    use SoftDeletes, Uploadable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['store_id','name','icon','cover','lat','long','address','num_rating','rate','citc_authority_id','category','offer','offer_image','offer_amount','offer_type','offer_max','available','join_request','seen','status','special','has_contract','user_id','app_commission','commission_type','commercial_id','commercial_image','bank_name','iban_number'];
    protected $guarded = [];

    public $translatable = ['name'];
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
    public function scopeSearch($query, $searchArray = [])
    {
        $query->where(function ($query) use ($searchArray) {
            if ($searchArray) {
                foreach ($searchArray as $key => $value) {
                    if (str_contains($key, '_id')) {
                        if (null != $value) {
                            $query->Where($key, $value);
                        }
                    } elseif ('order' == $key) {
                    } elseif ('created_at_min' == $key) {
                        if (null != $value) {
                            $query->WhereDate('created_at', '>=', $value);
                        }
                    } elseif ('created_at_max' == $key) {
                        if (null != $value) {
                            $query->WhereDate('created_at', '<=', $value);
                        }
                    }elseif ('name' == $key) {
                        if (null != $value) {
                            $query->Where('name->ar', $value)
                            ->orWhere('name->en',$value);
                        }
                    } else {
                        $query->Where($key, 'like', '%' . $value . '%');
                    }
                }
            }
        });
        return $query->orderBy('created_at', request()->searchArray && request()->searchArray['order'] ? request()->searchArray['order'] : 'DESC');
    }


    public function getIconPathAttribute()
    {
        return asset('assets/uploads/stores/' . $this->icon);
    }

    public function getCoverPathAttribute()
    {
        return asset('assets/uploads/stores/' . $this->cover);
    }

    public function getOfferPathAttribute()
    {
        return asset('assets/uploads/stores/' . $this->offer_image);
    }

    public function getCommercialImagePathAttribute()
    {
        return asset('assets/uploads/stores/' . $this->commercial_image);
    }

    public static function boot() {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */


        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['cover'], 'sponsers');

        });
    }

    public function isFather()
    {
        return is_null($this->attributes['store_id']);
    }

    protected $appends = ['is_father'];

    public function getIsFatherAttribute()
    {
        return is_null($this->attributes['store_id']);
    }


    public function parent()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function branches()
    {
        return $this->hasMany(Store::class);
    }

    public function timings(){
        return $this->hasMany(StoreTiming::class);
    }

    public function reviews(){
        return $this->hasMany(Review::class,'store_id');
    }


    public function rating(){
        $reviewsCount = $this->reviews()->count();
        if($reviewsCount == 0){
            return number_format(0,2);
        }
        $reviews = $this->reviews;
        $sum = 0;
        foreach($reviews as $review){
            $sum+=$review->rate;
        }
        return number_format(round($sum/$reviewsCount,2),2);
    }

    public function openingHours($lang = 'ar'){
        $opening_hours_ = "";
        $days = ['saturday','sunday','monday','tuesday','wednesday','thursday','friday'];
        $open_status = false;
        $current_day = lcfirst(date('l'));
        $opening_hours_arr = [];
        foreach($days as $day){
            $timings  = $this->timings()->where('day',$day)->get();
            if(count($timings) == 0){
                $opening_hours_arr[]=[
                    'day'   => trans('stores.'.$day),
                    'time'  => trans('stores.closed')
                ];
            }else{
                $i = 0;
                $opening_hours_= trans('stores.'.$day).": ";
                foreach($timings as $timing){
                    $from = $lang == 'ar'? ($timing->from > 11 ? date('h:i \م',strtotime($timing->from)) : date('h:i \ص',strtotime($timing->from))) : date('h:i a',strtotime($timing->from));
                    $fromEN=date('h:i a',strtotime($timing->from));
                    $toEN=date('h:i a',strtotime($timing->to));
                    $to =  $lang == 'ar' ? ($timing->to > 11 ? date('h:i \م',strtotime($timing->to)) : date('h:i \ص',strtotime($timing->to))) : date('h:i a',strtotime($timing->to)) ;
                    if(++$i === count($timings)) {
                        $opening_hours_arr[]=[
                            'day'   => trans('stores.'.$day),
                            'time'  => $from." ".trans('stores.to')." ".$to
                        ];
                    }else{
                        $opening_hours_arr[]=[
                            'day'   => trans('stores.'.$day),
                            'time'  => $from." ".trans('stores.to')." ".$to." ".trans('stores.and')." "
                        ];
                    }
                    if($day == $current_day){
                        $current_time = date('h:i a');
                        $date1 = DateTime::createFromFormat('h:i a', $current_time);
                        $date2 = DateTime::createFromFormat('h:i a', $fromEN);
                        $date3 = DateTime::createFromFormat('h:i a', $toEN);
                        if ($date1 >= $date2 && $date1 <= $date3) {
                            $open_status = true;
                        }
                    }
                }
            }
        }
        $data = [
            'opening_hours_arr' =>$opening_hours_arr,
            'open_status'       =>$open_status
        ];
        return $data;
    }

    public function menuCategories(){
        return $this->hasMany(StoreMenuCategory::class);
    }

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function productAdditiveCategories(){
        return $this->hasMany(ProductAdditiveCategory::class);
    }


    /**
     * Get the user that owns the Store
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function cachedMenusWithProducts(){

           return Cache::rememberForever('store-' . $this->id, function ()  {
                return new StoreResource($this->load('menuCategories'));
            });
    }

    public function updateCacheWithProducts(){
        Cache::forget('store-'.$this->id);
        $this->cachedMenusWithProducts();
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'store_id');
    }
}
