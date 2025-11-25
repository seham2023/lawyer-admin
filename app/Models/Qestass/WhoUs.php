<?php

namespace App\Models;

use App\Traits\Uploadable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhoUs extends Model
{
    use HasFactory, Uploadable, HasTranslations;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'desc', 'image'];

    public $translatable = ['title', 'desc'];

    public function getImagePathAttribute()
    {
        return asset('assets/uploads/intros/' . $this->image);
    }

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function setImageAttribute($value)
    {
        $this->attributes['image'] = $this->uploadFile($value, 'intros');
    }


    public static function boot()
    {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['image'], 'intros');

        });
    }
}