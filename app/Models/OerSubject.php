<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OerSubject extends Model
{
    protected $table = 'oer_subjects';

    protected $fillable = [
        'code',
        'name',
        'description',
        'sort_order',
        'is_active'
    ];

    public function resources(): HasMany
    {
        return $this->hasMany(OpenEducationalResource::class, 'subject_id')->orderBy('sort_order');
    }
}
