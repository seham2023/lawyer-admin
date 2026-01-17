<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get the user that owns the setting.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include settings for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include a specific setting key.
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Get a setting value by key for a user.
     */
    public static function getValue(int $userId, string $key, $default = null)
    {
        $setting = static::forUser($userId)->byKey($key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value for a user.
     */
    public static function setValue(int $userId, string $key, $value): self
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Delete a setting for a user.
     */
    public static function deleteSetting(int $userId, string $key): bool
    {
        return static::forUser($userId)->byKey($key)->delete();
    }
}
