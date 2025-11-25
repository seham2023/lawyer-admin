<?php

namespace App\Models;

use App\Traits\Uploadable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory, HasTranslations , Uploadable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name' , 'image' , 'slug' , 'category_id' , 'status','description'];
    public $translatable = ['name','description'];

    public function getImagePathAttribute()
    {
        return asset('assets/uploads/categories/' . $this->image);
    }

    public function setImageAttribute($value) {
        if ( null != $value &&  is_file($value) ) {
            $this->attributes['image'] = $this->uploadOne($value, 'categories');
        }
    }

    public static function boot() {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['image'], 'categories');

        });
    }
}
