<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatronSurvey extends Model
{
    use HasFactory;

    protected $table = 'patron_surveys';

    protected $fillable = [
        'full_name',
        'card_number',
        'email_phone',
        'patron_group',
        'rating_overall',
        'survey_category',
        'content',
        'status',
    ];

    protected $casts = [
        'rating_overall' => 'integer',
    ];

    public function ratings()
    {
        return $this->hasMany(PatronSurveyRating::class, 'patron_survey_id');
    }

    public function criteria()
    {
        return $this->belongsToMany(SurveyCriterion::class, 'patron_survey_ratings', 'patron_survey_id', 'survey_criterion_id')
                    ->withPivot('rating')
                    ->withTimestamps();
    }
}
