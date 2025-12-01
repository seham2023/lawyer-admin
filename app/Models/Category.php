<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasTranslations;
    protected $fillable = ['name', 'image', 'parent_id', 'type', 'user_id'];
    protected $translatable = ['name'];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function scopeClient(Builder $query): Builder
    {
        return $query->where('type', 'client');
    }

    public function scopeCase(Builder $query): Builder
    {
        return $query->where('type', 'case');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    public function scopeClientType(Builder $query): Builder
    {
        return $query->where('type', 'client_type');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\Qestass\User::class);
    }
}
