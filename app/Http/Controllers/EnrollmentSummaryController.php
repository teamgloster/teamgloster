<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Services\EnrollmentSummaryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EnrollmentSummaryController extends Controller
{
    public function adminIndex(Request $request, EnrollmentSummaryService $summaries): Response
    {
        return $this->index($request, $summaries, 'admin');
    }

    public function registrarIndex(Request $request, EnrollmentSummaryService $summaries): Response
    {
        return $this->index($request, $summaries, 'registrar');
    }

    private function index(Request $request, EnrollmentSummaryService $summaries, string $viewer): Response
    {
        $currentSchoolYear = SchoolSetting::currentSchoolYear();
        $schoolYears = $summaries->availableSchoolYears($currentSchoolYear);
        $requested = (string) $request->query('school_year', $currentSchoolYear);
        $schoolYear = in_array($requested, $schoolYears, true) ? $requested : $currentSchoolYear;

        return Inertia::render('Dashboard/EnrollmentSummary/Index', [
            'user' => $request->user(),
            'viewer' => $viewer,
            'schoolYear' => $schoolYear,
            'schoolYears' => $schoolYears,
            'summary' => $summaries->summarize($schoolYear),
        ]);
    }
}
