<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productfeatureproperity extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['productfeature_id' , 'properity_id'];
    
    public function productfeature(){
        return $this->belongsTo(Productfeature::class);
    }

    public function properity(){
        return $this->belongsTo(Properity::class);
    }
}
