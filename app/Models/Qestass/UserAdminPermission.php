<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAdminPermission extends Model
{
    use HasFactory;

    protected $fillable = ['admin_permission_id','user_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function permission(){
        return $this->belongsTo(AdminPermission::class);
    }

}
