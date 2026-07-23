<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalFolder extends Model
{
    protected $fillable = [
        'folder_code',
        'folder_name',
        'description',
        'parent_id',
        'sort_order',
        'is_active',
        'language'
    ];

    public function getNameAttribute(): string
    {
        return $this->folder_name ?? '';
    }

    public function resources(): HasMany
    {
        return $this->hasMany(DigitalResource::class, 'folder_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DigitalFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(DigitalFolder::class, 'parent_id')->orderBy('sort_order');
    }
}
