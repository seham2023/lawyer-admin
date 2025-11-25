<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderService extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'coupon',
        'type',
        'payment_type',
        'description',
        'price',
        'added_value',
        'app_percentage',
        'admin_commission_value',
        'admin_commission_percentage',
        'added_value',
        'discount',
        'total_price',
        'user_id',
        'payment_status',
        'status',
        'room_id',
    ];

}
