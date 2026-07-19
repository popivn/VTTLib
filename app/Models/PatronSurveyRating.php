<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatronSurveyRating extends Model
{
    use HasFactory;

    protected $table = 'patron_survey_ratings';

    protected $fillable = [
        'patron_survey_id',
        'survey_criterion_id',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function survey()
    {
        return $this->belongsTo(PatronSurvey::class, 'patron_survey_id');
    }

    public function criterion()
    {
        return $this->belongsTo(SurveyCriterion::class, 'survey_criterion_id');
    }
}
