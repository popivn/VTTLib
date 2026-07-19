<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatronSurvey;
use App\Models\SurveyCriterion;
use Illuminate\Http\Request;

class PatronSurveyController extends Controller
{
    /**
     * Display a listing of patron surveys in admin panel.
     */
    public function index(Request $request)
    {
        $query = PatronSurvey::with(['ratings.criterion']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('card_number', 'like', "%{$search}%")
                  ->orWhere('email_phone', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Rating filter
        if ($request->filled('rating')) {
            $query->where('rating_overall', $request->rating);
        }

        // Stats summary
        $totalSurveys = PatronSurvey::count();
        $avgRating = round(PatronSurvey::avg('rating_overall') ?? 0, 1);
        $fiveStarCount = PatronSurvey::where('rating_overall', 5)->count();
        $fourStarCount = PatronSurvey::where('rating_overall', 4)->count();
        $threeStarCount = PatronSurvey::where('rating_overall', 3)->count();

        $surveys = $query->latest()->paginate(15)->withQueryString();
        $activeCriteria = SurveyCriterion::active()->orderBy('sort_order')->get();

        return view('admin.surveys.index', compact(
            'surveys',
            'totalSurveys',
            'avgRating',
            'fiveStarCount',
            'fourStarCount',
            'threeStarCount',
            'activeCriteria'
        ));
    }
}
