<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseCourtHistory extends Model
{
    protected $table = 'case_court_history';

    protected $fillable = [
        'case_record_id',
        'court_id',
        'transfer_date',
        'transfer_reason',
        'notes',
        'is_current',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * Get the case record that owns this court history entry.
     */
    public function caseRecord()
    {
        return $this->belongsTo(CaseRecord::class);
    }

    /**
     * Get the court for this history entry.
     */
    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}
