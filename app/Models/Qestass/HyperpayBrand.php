<?php

namespace App\Models;

use App\Traits\Uploadable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HyperpayBrand extends Model
{
    use HasFactory, HasTranslations , Uploadable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name' , 'brand'  , 'image' , 'entity_id' , 'is_active'];
    public $translatable = ['name'];

    public function getImagePathAttribute(){
        return asset('assets/uploads/hyperpay_brands/' . $this->image);
    }
    public function setImageAttribute($value) {
        if (null != $value && is_file($value)) {
            $this->attributes['image'] = $this->uploadFile($value, 'hyperpay_brands');
        }
    }

    public static function boot() {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['image'], 'hyperpay_brands');

        });
    }
}
