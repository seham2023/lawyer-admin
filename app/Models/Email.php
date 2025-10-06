<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $fillable = [
        'email_template_id',
        'client_id',
        'subject',
        'content',
        'priority',
        'notes',
        'file_path',
    ];
    protected $casts = [
        'is_starred' => 'boolean',
      ];
    /**
     * Get the email template associated with the email.
     */
    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    /**
     * Get the client associated with the email.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
