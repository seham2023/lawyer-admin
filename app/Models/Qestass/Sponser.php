<?php

namespace App\Models;

use App\Traits\Uploadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sponser extends Model
{
    use HasFactory , Uploadable ;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name' , 'image' , 'url'];
    public $translatable = ['title'];



    public function getImagePathAttribute()
    {
        return asset('assets/uploads/sponsers/' . $this->image);
    }

    public function setImageAttribute($value) {
        if ($value != null && is_file($value)) {
            $this->attributes['image'] = $this->uploadFile($value, 'sponsers');
        }
    }

    public static function boot() {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['image'], 'sponsers');

        });
    }

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
