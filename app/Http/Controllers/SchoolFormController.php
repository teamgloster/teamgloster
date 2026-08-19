<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\SchoolFormService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SchoolFormController extends Controller
{
    public function adminIndex(): Response
    {
        $settings = SchoolSetting::current();
        $schoolYear = $settings->current_school_year;

        $sections = Section::query()
            ->with(['yearLevel', 'adviser'])
            ->where('school_year', $schoolYear)
            ->withCount([
                'enrollments as enrolled_count' => function ($query) use ($schoolYear) {
                    $query->where('school_year', $schoolYear)
                        ->whereIn('status', ['enrolled', 'approved']);
                },
            ])
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get()
            ->map(function (Section $section) {
                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'year_level' => $section->yearLevel?->name,
                    'adviser' => $section->adviser
                        ? trim($section->adviser->first_name.' '.$section->adviser->last_name)
                        : null,
                    'enrolled_count' => $section->enrolled_count,
                ];
            });

        $yearLevels = YearLevel::query()
            ->ordered()
            ->get(['id', 'name', 'code']);

        $enrollments = Enrollment::query()
            ->where('school_year', $schoolYear)
            ->whereIn('status', ['enrolled', 'approved'])
            ->with('yearLevel')
            ->get()
            ->keyBy('user_id');

        $students = User::query()
            ->where('role', 'student')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix', 'lrn', 'gender'])
            ->map(function (User $student) use ($enrollments) {
                $enrollment = $enrollments->get($student->id);

                return [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'suffix' => $student->suffix,
                    'lrn' => $student->lrn,
                    'gender' => $student->gender,
                    'year_level_id' => $enrollment?->year_level_id,
                    'year_level' => $enrollment?->yearLevel?->name,
                ];
            });

        return Inertia::render('Dashboard/Admin/SchoolForms', [
            'user' => auth()->user(),
            'schoolYear' => $schoolYear,
            'currentTerm' => $settings->current_term,
            'yearLevels' => $yearLevels,
            'sections' => $sections,
            'students' => $students,
        ]);
    }

    public function adminSf1(Section $section, SchoolFormService $forms): Response
    {
        return $this->preview('Dashboard/SchoolForms/Sf1', $forms->sf1($section), 'admin', route('admin.school-forms'));
    }

    public function adminSf2(Request $request, Section $section, SchoolFormService $forms): Response
    {
        return $this->preview(
            'Dashboard/SchoolForms/Sf2',
            $forms->sf2($section, (int) $request->query('month', now()->month)),
            'admin',
            route('admin.school-forms')
        );
    }

    public function adminSf9(Request $request, User $student, SchoolFormService $forms): Response
    {
        abort_unless($student->role === 'student', 404);

        return $this->preview(
            'Dashboard/SchoolForms/Sf9',
            $forms->sf9($student, $request->query('school_year'), $this->requestedTerm($request)),
            'admin',
            route('admin.school-forms')
        );
    }

    public function adminSf10(User $student, SchoolFormService $forms): Response
    {
        abort_unless($student->role === 'student', 404);

        return $this->preview('Dashboard/SchoolForms/Sf10', $forms->sf10($student), 'admin', route('admin.school-forms'));
    }

    public function studentSf9(Request $request, SchoolFormService $forms): Response
    {
        $student = $request->user();
        abort_unless($student?->role === 'student', 403);

        return $this->preview(
            'Dashboard/SchoolForms/Sf9',
            $forms->sf9($student, $request->query('school_year'), $this->requestedTerm($request)),
            'student',
            route('student.grades')
        );
    }

    public function studentSf10(Request $request, SchoolFormService $forms): Response
    {
        $student = $request->user();
        abort_unless($student?->role === 'student', 403);

        return $this->preview('Dashboard/SchoolForms/Sf10', $forms->sf10($student), 'student', route('student.grades'));
    }

    public function teacherSf1(Request $request, Section $section, SchoolFormService $forms): Response
    {
        $this->assertAdvisorySection($request->user(), $section);

        return $this->preview(
            'Dashboard/SchoolForms/Sf1',
            $forms->sf1($section),
            'teacher',
            '/dashboard/teacher?nav=school-forms'
        );
    }

    public function teacherSf2(Request $request, Section $section, SchoolFormService $forms): Response
    {
        $this->assertAdvisorySection($request->user(), $section);

        return $this->preview(
            'Dashboard/SchoolForms/Sf2',
            $forms->sf2($section, (int) $request->query('month', now()->month)),
            'teacher',
            '/dashboard/teacher?nav=school-forms'
        );
    }

    public function teacherSf9(Request $request, User $student, SchoolFormService $forms): Response
    {
        $this->assertAdvisoryStudent($request->user(), $student);

        return $this->preview(
            'Dashboard/SchoolForms/Sf9',
            $forms->sf9($student, $request->query('school_year'), $this->requestedTerm($request)),
            'teacher',
            '/dashboard/teacher?nav=school-forms'
        );
    }

    public function teacherSf10(Request $request, User $student, SchoolFormService $forms): Response
    {
        $this->assertAdvisoryStudent($request->user(), $student);

        return $this->preview(
            'Dashboard/SchoolForms/Sf10',
            $forms->sf10($student),
            'teacher',
            '/dashboard/teacher?nav=school-forms'
        );
    }

    private function requestedTerm(Request $request): ?int
    {
        $term = $request->query('term');

        return in_array($term, ['1', '2', '3', 1, 2, 3], true) ? (int) $term : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function preview(string $component, array $payload, string $viewer, string $backUrl): Response
    {
        return Inertia::render($component, array_merge($payload, [
            'user' => auth()->user(),
            'viewer' => $viewer,
            'backUrl' => $backUrl,
        ]));
    }

    private function assertAdvisoryStudent(User $teacher, User $student): void
    {
        abort_unless($student->role === 'student', 404);

        $sectionIds = Section::query()
            ->where('adviser_id', $teacher->id)
            ->pluck('id');

        $isAdvisory = Enrollment::query()
            ->where('user_id', $student->id)
            ->whereIn('section_id', $sectionIds)
            ->whereIn('status', ['enrolled', 'approved'])
            ->exists();

        abort_unless($isAdvisory, 403);
    }

    private function assertAdvisorySection(User $teacher, Section $section): void
    {
        abort_unless((int) $section->adviser_id === (int) $teacher->id, 403);
    }
}
