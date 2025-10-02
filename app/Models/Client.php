<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use  SoftDeletes;

    // Fillable fields
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'address_id',
        'gender',
        'notes',
        'category_id',
        'company',
    ];

    // Relationship with Address
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    // Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

   
}
