<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'name',  'description', 'file_path','case_record_id','client_id'
    ];
    
}
