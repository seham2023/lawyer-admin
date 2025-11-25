<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticketimage extends Model
{
    use HasFactory;

    protected $fillable = ['ticket_id','image'];


    public function getImagePathAttribute(){
        return asset('assets/uploads/tickets/' . $this->image);
    }

    public static function boot() {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['image'], 'tickets');

        });
    }

}
