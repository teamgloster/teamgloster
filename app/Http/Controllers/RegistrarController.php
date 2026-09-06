<?php

namespace App\Http\Controllers;

use App\Exceptions\StudentPromotionException;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\GradeRecordService;
use App\Services\StudentPromotionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrarController extends Controller
{
    public function dashboard(): Response
    {
        $schoolYear = SchoolSetting::currentSchoolYear();

        $stats = [
            'yearLevels' => YearLevel::query()->count(),
            'sections' => Section::query()->where('school_year', $schoolYear)->count(),
            'enrolled' => Enrollment::query()->where('school_year', $schoolYear)->where('status', 'enrolled')->count(),
            'gradeRecords' => Grade::query()->where('school_year', $schoolYear)->count(),
        ];

        $enrollmentByYearLevel = YearLevel::query()
            ->withCount(['enrollments' => function ($query) use ($schoolYear) {
                $query->where('school_year', $schoolYear)->where('status', 'enrolled');
            }, 'sections' => function ($query) use ($schoolYear) {
                $query->where('school_year', $schoolYear);
            }])
            ->ordered()
            ->get();

        return Inertia::render('Dashboard/Registrar/Index', [
            'user' => auth()->user(),
            'stats' => $stats,
            'enrollmentByYearLevel' => $enrollmentByYearLevel,
            'currentSchoolYear' => $schoolYear,
        ]);
    }

    public function yearLevels(): Response
    {
        $schoolYear = SchoolSetting::currentSchoolYear();

        $yearLevels = YearLevel::query()
            ->withCount([
                'sections' => function ($query) use ($schoolYear) {
                    $query->where('school_year', $schoolYear);
                },
                'subjects',
                'enrollments' => function ($query) use ($schoolYear) {
                    $query->where('school_year', $schoolYear)->where('status', 'enrolled');
                },
            ])
            ->ordered()
            ->get();

        return Inertia::render('Dashboard/Registrar/YearLevels', [
            'user' => auth()->user(),
            'yearLevels' => $yearLevels,
            'currentSchoolYear' => $schoolYear,
        ]);
    }

    public function sections(): Response
    {
        $schoolYear = SchoolSetting::currentSchoolYear();

        $sections = Section::query()
            ->with(['yearLevel', 'adviser'])
            ->withCount(['enrollments' => function ($query) {
                $query->where('status', 'enrolled');
            }])
            ->where('school_year', $schoolYear)
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get();

        $yearLevels = YearLevel::ordered()->get();

        return Inertia::render('Dashboard/Registrar/Sections', [
            'user' => auth()->user(),
            'sections' => $sections,
            'yearLevels' => $yearLevels,
            'currentSchoolYear' => $schoolYear,
        ]);
    }

    public function grades(GradeRecordService $gradeRecords): Response
    {
        return Inertia::render('Dashboard/Registrar/Grades', [
            'user' => auth()->user(),
            ...$gradeRecords->pageData(),
        ]);
    }

    public function students(Request $request): Response
    {
        $settings = SchoolSetting::current();
        $currentSchoolYear = $settings->current_school_year;
        $availableSchoolYears = Section::query()
            ->select('school_year')
            ->distinct()
            ->orderByDesc('school_year')
            ->pluck('school_year')
            ->filter()
            ->values();

        if ($availableSchoolYears->isEmpty()) {
            $availableSchoolYears = collect([$currentSchoolYear]);
        } elseif (! $availableSchoolYears->contains($currentSchoolYear)) {
            $availableSchoolYears->prepend($currentSchoolYear);
        }

        $schoolYear = (string) $request->query('sy', '');
        if ($schoolYear === '') {
            $yearWithMostSections = Section::query()
                ->selectRaw('school_year, COUNT(*) as total')
                ->where('is_active', true)
                ->groupBy('school_year')
                ->orderByDesc('total')
                ->orderByDesc('school_year')
                ->value('school_year');

            $schoolYear = $yearWithMostSections ?: $currentSchoolYear;
        }

        if (! $availableSchoolYears->contains($schoolYear)) {
            $schoolYear = $currentSchoolYear;
        }

        $sections = Section::query()
            ->with('yearLevel')
            ->withCount([
                'enrollments as enrolled_count' => function ($query) use ($schoolYear) {
                    $query->where('school_year', $schoolYear)
                        ->whereIn('status', ['enrolled', 'approved']);
                },
            ])
            ->where('school_year', $schoolYear)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $unassignedCount = Enrollment::query()
            ->where('school_year', $schoolYear)
            ->whereIn('status', ['enrolled', 'approved'])
            ->whereNull('section_id')
            ->count();

        return Inertia::render('Dashboard/Registrar/Students', [
            'user' => auth()->user(),
            'yearLevels' => YearLevel::query()->ordered()->get(),
            'sections' => $sections,
            'unassignedCount' => $unassignedCount,
            'currentSchoolYear' => $currentSchoolYear,
            'selectedSchoolYear' => $schoolYear,
            'availableSchoolYears' => $availableSchoolYears,
            'enrollmentOpen' => (bool) $settings->enrollment_open,
        ]);
    }

    public function sectionEnrollment(Section $section, StudentPromotionService $promotionService): Response
    {
        $schoolYear = SchoolSetting::currentSchoolYear();
        $section->load('yearLevel');

        $enrollments = Enrollment::query()
            ->with(['user', 'yearLevel', 'section'])
            ->where('section_id', $section->id)
            ->where('school_year', $section->school_year ?: $schoolYear)
            ->whereIn('status', ['enrolled', 'approved'])
            ->get()
            ->sortBy(fn (Enrollment $enrollment) => strtoupper(
                trim(($enrollment->user?->last_name ?? '').' '.($enrollment->user?->first_name ?? ''))
            ))
            ->values();

        $students = $enrollments
            ->map(function (Enrollment $enrollment) {
                $student = $enrollment->user;
                if (! $student) {
                    return null;
                }

                $student->setRelation('currentEnrollment', $enrollment);

                return $student;
            })
            ->filter()
            ->values();

        $promotionService->attachSummaries($students, $schoolYear);

        return Inertia::render('Dashboard/Registrar/SectionEnrollment', [
            'user' => auth()->user(),
            'section' => $section,
            'students' => $students,
            'yearLevels' => YearLevel::query()->ordered()->get(),
            'currentSchoolYear' => $schoolYear,
        ]);
    }

    public function studentEnrollment(User $student, StudentPromotionService $promotionService): Response
    {
        abort_unless($student->role === 'student', 404);

        $schoolYear = SchoolSetting::currentSchoolYear();

        $history = Enrollment::query()
            ->with(['yearLevel', 'section', 'approvedBy:id,first_name,last_name'])
            ->where('user_id', $student->id)
            ->orderByDesc('school_year')
            ->orderByDesc('id')
            ->get();

        $current = $history->first(
            fn (Enrollment $enrollment) => $enrollment->school_year === $schoolYear
        );

        $student->setRelation('currentEnrollment', $current);
        $student->setRelation('enrollments', $history);
        $promotionService->attachSummaries([$student], $schoolYear);

        return Inertia::render('Dashboard/Registrar/EnrollmentDetails', [
            'user' => auth()->user(),
            'student' => $student,
            'currentEnrollment' => $current,
            'enrollments' => $history,
            'currentSchoolYear' => $schoolYear,
        ]);
    }

    public function promoteStudent(Request $request, User $student, StudentPromotionService $promotionService)
    {
        if ($student->role !== 'student') {
            abort(404);
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $enrollment = $promotionService->promote($student, $validated['remarks'] ?? null);
        } catch (StudentPromotionException $exception) {
            return back()->withErrors([
                'promotion' => $exception->getMessage(),
            ]);
        }

        $yearLevelName = $enrollment->yearLevel?->name ?? 'the next year level';

        return back()->with('success', $student->first_name.' '.$student->last_name.' was promoted to '.$yearLevelName.'.');
    }

    public function changeStudentYearLevel(Request $request, User $student, StudentPromotionService $promotionService)
    {
        if ($student->role !== 'student') {
            abort(404);
        }

        $validated = $request->validate([
            'year_level_id' => 'required|exists:year_levels,id',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $enrollment = $promotionService->changeYearLevel(
                $student,
                (int) $validated['year_level_id'],
                $validated['remarks'] ?? null,
            );
        } catch (StudentPromotionException $exception) {
            return back()->withErrors([
                'promotion' => $exception->getMessage(),
            ]);
        }

        $yearLevelName = $enrollment->yearLevel?->name ?? 'the selected year level';

        return back()->with('success', $student->first_name.' '.$student->last_name.' was moved to '.$yearLevelName.'.');
    }

    public function promoteSelected(Request $request, StudentPromotionService $promotionService)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:users,id',
        ]);

        $students = User::query()
            ->where('role', 'student')
            ->whereIn('id', $validated['student_ids'])
            ->get();

        $result = $promotionService->promoteMany($students);

        if ($result['promoted'] === 0) {
            return back()->withErrors([
                'promotion' => 'None of the selected students could be promoted. Students must have passing final grades in all subjects of their current year level.',
            ]);
        }

        return back()->with('success', $result['message']);
    }
}
