<?php

namespace App\Models\Qestass;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class AdminPermission extends Model
{
    use HasFactory, HasTranslations ;

    protected $connection = 'qestass_app';
    protected $fillable = ['name'  , 'slug' ];
    public $translatable = ['name'];

}
