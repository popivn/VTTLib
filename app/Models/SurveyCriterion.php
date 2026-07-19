<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyCriterion extends Model
{
    use HasFactory;

    protected $table = 'survey_criteria';

    protected $fillable = [
        'code',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function ratings()
    {
        return $this->hasMany(PatronSurveyRating::class, 'survey_criterion_id');
    }
}
