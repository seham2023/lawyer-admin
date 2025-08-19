<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
  public string $tableName = 'admin-table-tcoecu-table';

  use HasFactory, HasRoles, Notifiable, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name',
    'email',
    'username',
    'phone',
    'active',
    'password',
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'active' => 'boolean',
  ];


  /**
   * Automatically hash the password when it is set.
   *
   * @param string $value
   * @return void
   */
  public function setPasswordAttribute(string $value): void
  {
    $this->attributes['password'] = bcrypt($value);
  }

  /**
   * @param Builder $builder
   * @return Builder
   */
  public function scopeActive(Builder $builder): Builder
  {
    return $builder->where('active', true);
  }


  /**
   * Additional functionality: Admin has profile picture.
   *
   * @param int $size
   * @param string $fontColor
   * @param bool $rounded
   * @return string
   */
  public function getAvatar(int $size = 128, string $fontColor = 'fff', bool $rounded = true): string
  {
    if ($this->profile_picture) {
      return $this->profile_picture;
    }

    $cacheKey = 'user_avatar_color_' . $this->id;
    $cacheDuration = now()->addHours(6);

    $backgroundColor = Cache::remember($cacheKey, $cacheDuration, function () {
      $colors = ['007bff', 'ff5733', '28a745', '6f42c1', 'fd7e14'];
      return $colors[array_rand($colors)];
    });

    $initials = collect(explode(' ', $this->name))
      ->map(fn($word) => strtoupper($word[0]))
      ->join('');

    return "https://ui-avatars.com/api/?" . http_build_query([
        'name' => $initials,
        'size' => $size,
        'background' => $backgroundColor,
        'color' => $fontColor,
        'rounded' => $rounded ? 'true' : 'false',
        'format' => 'svg',
      ]);
  }

}
