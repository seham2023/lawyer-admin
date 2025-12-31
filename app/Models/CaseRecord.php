<?php

namespace App\Models;

use App\Models\Qestass\User;
use Illuminate\Database\Eloquent\Model;

class CaseRecord extends Model
{
    protected $fillable = [
        'category_id',
        'status_id',
        'user_id',
        'opponent_id',
        'opponent_lawyer_id',
        'start_date',
        'level_id',
        'court_id',
        'court_name',
        'court_number',
        'lawyer_name',
        'judge_name',
        'location',
        'subject',
        'subject_description',
        'notes',
        'contract',
        'client_type_id',
        'client_id'
    ];
    protected $casts = [
        'start_date' => 'date',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id', 'id');
    }
    public function opponent()
    {
        return $this->belongsTo(Opponent::class);
    }

    public function opponent_lawyer()
    {
        return $this->belongsTo(OpponentLawyer::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function client_type()
    {
        return $this->belongsTo(Category::class, 'client_type_id');
    }

    /**
     * Get all court history records for this case.
     */
    public function courtHistory()
    {
        return $this->hasMany(CaseCourtHistory::class, 'case_record_id');
    }

    /**
     * Get the current court for this case.
     */
    public function currentCourt()
    {
        return $this->hasOne(CaseCourtHistory::class, 'case_record_id')
            ->where('is_current', true)
            ->with('court');
    }

    /**
     * Legacy court relationship - kept for backward compatibility.
     * Use currentCourt() or courtHistory() instead.
     * @deprecated
     */
    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * Get the primary payment for this case record.
     */
    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'case_record_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'case_record_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'case_record_id');
    }

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class, 'case_record_id');
    }

    public function paymentSessions()
    {
        return $this->hasMany(PaymentSession::class, 'case_record_id');
    }

    public function __toString()
    {
        return $this->subject;
    }
}
