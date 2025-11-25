<?php

namespace App\Models;

use App\Traits\Uploadable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SiteAdvantage extends Model
{
    use HasFactory  , HasTranslations ,Uploadable ;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['icon','title','desc'];

    public $translatable = ['title','desc'];

    public function getIconPathAttribute()
    {
        return asset('assets/uploads/settings/' . $this->icon);
    }   

    public function setIconAttribute($value) {
        if (is_file($value)) {
            $this->attributes['icon'] = $this->uploadOne($value, 'settings');
        }
    }

    public static function boot() {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['icon'], 'settings');

        });
    }

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }


}
