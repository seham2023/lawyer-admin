<?php

namespace App\Models;

use App\Traits\Uploadable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppPage extends Model
{
    use HasFactory  ,Uploadable ;
    protected $fillable = ['image'];

    public function getImagePathAttribute()
    {
        return asset('assets/uploads/settings/' . $this->image);
    }
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }


    public function setImageAttribute($value) {
        if (is_file($value)) {
            $this->attributes['image'] = $this->uploadOne($value, 'settings');
        }
    }


    public static function boot() {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['image'], 'settings');

        });

    }
}
