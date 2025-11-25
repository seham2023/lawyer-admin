<?php

namespace App\Models;

use App\Traits\Uploadable;
use App\Traits\UploadTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\File;

class Admin extends Authenticatable {
  use Notifiable, SoftDeletes , Uploadable ;

  protected $fillable = [
    'name',
    'phone',
    'email',
    'password',
    'avatar',
    'role_id',
    'is_notify',
    'blocked',
  ];

  protected $hidden = [
    'password',
  ];

  public function getAvatarAttribute() {
      $avatar = $this->attributes['avatar'] == NULL ? 'default.png' : $this->attributes['avatar'];
      return asset('assets/uploads/admins/' . $avatar);
  }

  public function setAvatarAttribute($value) {
      if($value != 'default.png' && $value != null)
      {
          $this->attributes['avatar'] = $this->uploadFile($value, 'admins', true, 250, null);
      }
  }
  

  public function role() {
    return $this->belongsTo(Role::class)->withTrashed();
  }

  public function setPasswordAttribute($value) {
    if (null != $value) {
      $this->attributes['password'] = bcrypt($value);
    }
  }

  public function replays() {
    return $this->morphMany(ComplaintReplay::class, 'replayer');
  }

  public static function boot() {
    parent::boot();
    /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

    self::deleted(function ($model) {
      $model->deleteFile($model->attributes['avatar'], 'admins');
    });

  }

}
