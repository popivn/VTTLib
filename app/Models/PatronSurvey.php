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
        'rating_service',
        'rating_resource',
        'rating_facility',
        'rating_overall',
        'survey_category',
        'content',
        'status',
    ];

    protected $casts = [
        'rating_service' => 'integer',
        'rating_resource' => 'integer',
        'rating_facility' => 'integer',
        'rating_overall' => 'integer',
    ];
}
