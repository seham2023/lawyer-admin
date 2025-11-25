<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Paymentmethod extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['name','desc'];

    public function getImagePathAttribute()
    {
        return asset('assets/uploads/paymentmethods/' . $this->image);
    }

    public static function boot() {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['image'], 'paymentmethods');

        });

    }

}
