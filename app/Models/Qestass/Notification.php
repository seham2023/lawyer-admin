<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'notifiable_id');
    }

    public function getTitleAttribute()
    {
        if (isset($this->data['title']) && $this->type == 'from_admin')
            return $this->data['title'];

        return 'from_admin';
    }

    public function getContentAttribute()
    {
        if (isset($this->data['message']) && $this->type == 'from_admin')
            return $this->data['message'];

        return 'from_admin';
    }

    public function getTypeTransAttribute()
    {
        if ($this->type == 'from_admin')
            return __('notifications.from_admin');
        elseif ($this->type == 'new_order')
            return __('notifications.new_order');
        else
            return '';
    }

    public function getUrlAttribute()
    {
        if (isset($this->data['route_name']) && isset($this->data['route_id']) )
            return route($this->data['route_name'],  $this->data['route_id']);
        elseif (isset($this->data['route_name']))
            return route($this->data['route_name']);
        else
            return '#';
    }




}
