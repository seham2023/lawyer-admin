<?php

namespace App\Models;

use App\Models\Qestass\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'period',
        'price',
        'name',
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function visits(): BelongsToMany
    {
        return $this->belongsToMany(Visit::class)->using(ServiceVisit::class);
    }
}
