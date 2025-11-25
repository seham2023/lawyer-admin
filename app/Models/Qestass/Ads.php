<?php

namespace App\Models;

use App\Traits\Uploadable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ads extends Model
{
        use HasFactory, HasTranslations , Uploadable ;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title','content','image','cover','expiry_date'];

    public $translatable = ['title','content','meta_title'];
    protected $table = 'ads';
    protected $guarded = [];


    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }



    public function setImageAttribute($value) {
        if (is_file($value)) {
            $this->attributes['image'] = $this->uploadOne($value, 'advantages');
        }
    }

    public function setCoverAttribute($value) {
        if (is_file($value)) {
            $this->attributes['cover'] = $this->uploadOne($value, 'advantages');
        }
    }


    public function getImagePathAttribute() {
        if ($this->attributes['image']) {
            $image = $this->getImage($this->attributes['image'], 'advantages');
        } else {
            $image = $this->defaultImage('advantages');
        }
        return $image;
    }


    public function getCoverPathAttribute() {
        if ($this->attributes['cover']) {
            $image = $this->getImage($this->attributes['cover'], 'advantages');
        } else {
            $image = $this->defaultImage('advantages');
        }
        return $image;
    }


    public static function boot() {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['cover'], 'advantages');
            $model->deleteFile($model->attributes['image'], 'advantages');

        });

    }

}
