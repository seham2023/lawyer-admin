<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{

    protected $table = 'case_sessions';
    protected $fillable = [
        'case_number',
        'title',
        'details',
        'datetime',
        'priority',
        'case_record_id',
        'user_id'
    ];

    protected $casts = [
    'datetime' => 'datetime',
    ];


    public function caseRecord(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class);
    }
}
