<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use App\Traits\Uploadable;

class Social extends Model
{
    use HasFactory ,Uploadable;
    protected $guarded = [];
    protected $table = 'socials';


    public function getImagePathAttribute()
    {
        return $this->image?asset('assets/uploads/socials/' . $this->image):'';
    }

    public function setImageAttribute($value)
    {
        if($this->image){
            File::delete(public_path('assets/uploads/socials/' . $this->image));
        }
        $this->attributes['image'] = $this->uploadFile($value, 'socials', true, 250, null);

    }

    public static function boot() {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['image'], 'socials');
        });
    }

}
