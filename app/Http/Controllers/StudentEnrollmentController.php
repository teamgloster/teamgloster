<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\Strand;
use App\Models\Subject;
use App\Models\YearLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StudentEnrollmentController extends Controller
{
    /**
     * Get enrollment data for the student dashboard
     */
    public function getEnrollmentData()
    {
        $user = Auth::user();
        $currentSchoolYear = $this->getCurrentSchoolYear();

        // Get current enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('school_year', $currentSchoolYear)
            ->with(['yearLevel', 'section', 'section.adviser'])
            ->first();

        // Get available year levels
        $yearLevels = YearLevel::active()->ordered()->get();

        // Get enrollment history
        $enrollmentHistory = Enrollment::where('user_id', $user->id)
            ->with(['yearLevel', 'section'])
            ->orderBy('school_year', 'desc')
            ->get();

        return [
            'currentEnrollment' => $enrollment,
            'yearLevels' => $yearLevels,
            'enrollmentHistory' => $enrollmentHistory,
            'currentSchoolYear' => $currentSchoolYear,
        ];
    }

    /**
     * Get sections for a specific year level
     */
    public function getSections(Request $request)
    {
        $yearLevelId = $request->input('year_level_id');
        $currentSchoolYear = $this->getCurrentSchoolYear();

        $sections = Section::where('year_level_id', $yearLevelId)
            ->where('school_year', $currentSchoolYear)
            ->where('is_active', true)
            ->withCount('students')
            ->with('adviser:id,first_name,last_name')
            ->get()
            ->map(function ($section) {
                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'code' => $section->code,
                    'capacity' => $section->capacity,
                    'current_students' => $section->students_count,
                    'available_slots' => $section->capacity - $section->students_count,
                    'adviser' => $section->adviser ? $section->adviser->first_name.' '.$section->adviser->last_name : 'TBA',
                ];
            });

        return response()->json($sections);
    }

    /**
     * Get subjects for a specific year level
     */
    public function getSubjects(Request $request)
    {
        $yearLevelId = $request->input('year_level_id');
        $semester = $request->input('semester', 'first');

        $subjects = Subject::where('year_level_id', $yearLevelId)
            ->where('is_active', true)
            ->when($request->filled('section_id'), fn ($query) => $query->forSection($request->integer('section_id')))
            ->where(function ($query) use ($semester) {
                $query->where('semester', $semester)
                    ->orWhere('semester', 'full_year');
            })
            ->orderBy('subject_type')
            ->orderBy('name')
            ->get();

        return response()->json($subjects);
    }

    /**
     * Submit enrollment application
     */
    public function submitEnrollment(Request $request)
    {
        $user = Auth::user();
        $currentSchoolYear = $this->getCurrentSchoolYear();

        if (! $user->isAdmissionApproved()) {
            $message = $user->admission_status === 'rejected'
                ? 'Your admission application was rejected. You cannot enroll at this time.'
                : 'Your admission is still pending approval. You cannot enroll until the registrar approves your application.';

            return back()->withErrors([
                'enrollment' => $message,
            ]);
        }

        if (! SchoolSetting::enrollmentOpen()) {
            return back()->withErrors([
                'enrollment' => 'Enrollment is currently closed. Please contact the school registrar.',
            ]);
        }

        // Check if already enrolled for this school year
        $existingEnrollment = Enrollment::where('user_id', $user->id)
            ->where('school_year', $currentSchoolYear)
            ->first();

        if ($existingEnrollment) {
            return back()->withErrors([
                'enrollment' => 'You have already submitted an enrollment for this school year.',
            ]);
        }

        $selectedYearLevel = YearLevel::query()->find($request->input('year_level_id'));
        $isSeniorHigh = $selectedYearLevel?->level_type === 'senior_high';

        $validated = $request->validate([
            'year_level_id' => 'required|exists:year_levels,id',
            'enrollment_type' => 'required|in:new,old,transferee,returnee',
            'previous_gwa' => 'nullable|numeric|min:70|max:100',
            'previous_school' => 'required_unless:enrollment_type,old|nullable|string|max:255',
            'preferred_strand' => [
                Rule::requiredIf($isSeniorHigh && Strand::query()->active()->exists()),
                'nullable',
                'string',
                'max:100',
                Rule::exists('strands', 'code')->where('is_active', true),
            ],
        ], [
            'previous_school.required_unless' => 'Please enter your previous / old school.',
            'preferred_strand.required' => 'Please choose an academic track.',
            'preferred_strand.exists' => 'Please choose a valid academic track.',
        ]);

        if ($validated['enrollment_type'] === 'old') {
            $validated['previous_school'] = null;
        }

        $previousYearLevel = $user->previousYearLevel($currentSchoolYear);

        if (
            $previousYearLevel
            && $selectedYearLevel
            && $selectedYearLevel->rank < $previousYearLevel->rank
        ) {
            return back()->withErrors([
                'year_level_id' => 'You cannot enroll below your previous year level ('.$previousYearLevel->name.'). Please select '.$previousYearLevel->name.' or higher.',
            ]);
        }

        // Create enrollment
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'year_level_id' => $validated['year_level_id'],
            'school_year' => $currentSchoolYear,
            'semester' => 'first',
            'status' => 'pending',
            'enrollment_type' => $validated['enrollment_type'],
            'previous_gwa' => $validated['previous_gwa'] ?? $user->previous_gwa,
            'previous_school' => $validated['previous_school'],
        ]);

        $userUpdates = [
            'previous_gwa' => $validated['previous_gwa'] ?? $user->previous_gwa,
        ];

        if ($isSeniorHigh && ! empty($validated['preferred_strand'])) {
            $userUpdates['preferred_strand'] = $validated['preferred_strand'];
        }

        $user->update($userUpdates);

        return back()->with('success', 'Enrollment application submitted successfully! Please wait for admin approval.');
    }

    /**
     * Cancel enrollment application
     */
    public function cancelEnrollment(Request $request)
    {
        $user = Auth::user();
        $currentSchoolYear = $this->getCurrentSchoolYear();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('school_year', $currentSchoolYear)
            ->where('status', 'pending')
            ->first();

        if (! $enrollment) {
            return back()->withErrors([
                'enrollment' => 'No pending enrollment found to cancel.',
            ]);
        }

        $enrollment->delete();

        return back()->with('success', 'Enrollment application cancelled successfully.');
    }

    /**
     * Get the active school year from Settings.
     */
    private function getCurrentSchoolYear(): string
    {
        return SchoolSetting::currentSchoolYear();
    }
}
