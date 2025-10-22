<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lawyer extends Model
{
    use SoftDeletes;

    // Fillable fields
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'address_id',
        'gender',
        'notes',
        'company',
        'bar_number', // Lawyer's bar registration number
        'specialization', // Area of expertise
        'license_status', // Active, suspended, etc.
    ];

    // Relationship with Address
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    // Relationship with Case Records (as representing lawyer)
    public function caseRecords()
    {
        return $this->hasMany(CaseRecord::class, 'lawyer_id'); // assuming there's a lawyer_id in case records
    }

    // Relationship with Opponent Cases (when acting as opponent lawyer)
    public function opponentCases()
    {
        return $this->hasMany(CaseRecord::class, 'opponent_lawyer_id');
    }

    public function __toString()
    {
        return $this->name;
    }
}
