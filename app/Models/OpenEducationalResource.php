<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenEducationalResource extends Model
{
    protected $table = 'open_educational_resources';

    protected $fillable = [
        'subject_id',
        'title',
        'slug',
        'author',
        'publisher',
        'description',
        'url',
        'thumbnail_url',
        'license',
        'language',
        'view_count',
        'download_count',
        'sort_order',
        'is_active'
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(OerSubject::class, 'subject_id');
    }
}
