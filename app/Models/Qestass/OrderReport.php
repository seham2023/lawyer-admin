<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReport extends Model
{
    use HasFactory;

    protected $fillable = ['name','order_id'];


    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function getImagePathAttribute()
    {
        return asset('assets/uploads/orders/' . $this->name);
    }

    public static function boot() {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['name'], 'orders');

        });

    }
}
