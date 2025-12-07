<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Court extends Model
{
    protected $fillable = [
        'name',
        'location',
        'court_number',
        'description',
        'category_id',
    ];

    public function caseRecords(): HasMany
    {
        return $this->hasMany(CaseRecord::class);
    }
}
