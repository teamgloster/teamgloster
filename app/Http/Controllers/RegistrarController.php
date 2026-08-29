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

    public function students(StudentPromotionService $promotionService): Response
    {
        $schoolYear = SchoolSetting::currentSchoolYear();

        $students = User::query()
            ->where('role', 'student')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $enrollments = Enrollment::query()
            ->with(['yearLevel', 'section'])
            ->where('school_year', $schoolYear)
            ->whereIn('user_id', $students->pluck('id'))
            ->orderByDesc('id')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $students->each(function (User $student) use ($enrollments) {
            $student->setRelation('currentEnrollment', $enrollments->get($student->id));
        });

        $promotionService->attachSummaries($students, $schoolYear);

        return Inertia::render('Dashboard/Registrar/Students', [
            'user' => auth()->user(),
            'students' => $students,
            'yearLevels' => YearLevel::ordered()->get(),
            'sections' => Section::query()
                ->with('yearLevel')
                ->where('school_year', $schoolYear)
                ->orderBy('year_level_id')
                ->orderBy('name')
                ->get(),
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
