<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseRecord extends Model
{
    protected $fillable = [
        'category_id',
        'status_id',
        'payment_id',
        'client_id',
        'opponent_id',
        'opponent_lawyer_id',
        'start_date',
        'level_id',
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
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
    
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id','id');
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
}
