<?php

namespace App\Models\Qestass;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Spetialist extends Model
{
    use HasFactory, HasTranslations;
    protected $fillable = ['name' ];
    public $translatable = ['name'];
}
