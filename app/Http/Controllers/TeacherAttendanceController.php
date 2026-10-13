<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolSetting;
use App\Models\User;
use App\Services\TeacherAttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TeacherAttendanceController extends Controller
{
    public function index(Request $request, TeacherAttendanceService $attendance): Response
    {
        $teacher = $this->teacher();
        $schoolYear = SchoolSetting::currentSchoolYear();
        $classes = $attendance->classesFor($teacher, $schoolYear);
        $date = $this->sheetDate($request->query('date'));
        $selectedKey = (string) $request->query('class', '');

        if ($selectedKey === '' || ! $classes->contains(fn (array $class) => $class['key'] === $selectedKey)) {
            $selectedKey = (string) ($classes->first()['key'] ?? '');
        }

        $selected = $classes->firstWhere('key', $selectedKey);
        $students = $selected
            ? $this->sheetStudents($attendance, $teacher, $schoolYear, $selectedKey, $date)
            : [];

        return Inertia::render('Dashboard/Teacher/Attendance', [
            'user' => $teacher,
            'currentSchoolYear' => $schoolYear,
            'classes' => $classes->values()->all(),
            'selectedClass' => $selectedKey,
            'date' => $date,
            'today' => $this->today(),
            'students' => $students,
        ]);
    }

    public function update(Request $request, TeacherAttendanceService $attendance)
    {
        $teacher = $this->teacher();
        $schoolYear = SchoolSetting::currentSchoolYear();

        $validated = $request->validate([
            'class_key' => ['required', 'string', 'max:80'],
            'date' => ['required', 'date'],
            'records' => ['required', 'array'],
            'records.*.student_id' => ['required', 'integer', 'distinct'],
            'records.*.status' => ['required', Rule::in(Attendance::STATUSES)],
            'records.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $date = Carbon::parse($validated['date'])->toDateString();
        if ($date > $this->today()) {
            throw ValidationException::withMessages([
                'date' => 'Attendance cannot be recorded for a future date.',
            ]);
        }

        $class = $attendance->classFor($teacher, $schoolYear, $validated['class_key']);
        abort_unless($class, 404);

        $roster = $attendance->studentsFor($teacher, $schoolYear, $validated['class_key']);
        $rosterIds = $roster->pluck('id')->map(fn ($id) => (int) $id);

        if ($rosterIds->isNotEmpty() && count($validated['records']) !== $rosterIds->count()) {
            throw ValidationException::withMessages([
                'records' => 'Mark every student in this class before saving.',
            ]);
        }

        $submittedIds = collect($validated['records'])->pluck('student_id')->map(fn ($id) => (int) $id);
        if ($submittedIds->diff($rosterIds)->isNotEmpty() || $rosterIds->diff($submittedIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'records' => 'Attendance can only be saved for students in this class.',
            ]);
        }

        foreach ($validated['records'] as $record) {
            Attendance::query()->updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'class_key' => $class['key'],
                    'attendance_date' => $date,
                ],
                [
                    'section_id' => $class['section_id'],
                    'subject_id' => $class['subject_id'],
                    'teacher_id' => $teacher->id,
                    'school_year' => $schoolYear,
                    'status' => $record['status'],
                    'remarks' => blank($record['remarks'] ?? null) ? null : $record['remarks'],
                ]
            );
        }

        return redirect()->route('teacher.attendance', [
            'class' => $class['key'],
            'date' => $date,
        ])->with('success', 'Attendance saved.');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function sheetStudents(
        TeacherAttendanceService $attendance,
        User $teacher,
        string $schoolYear,
        string $classKey,
        string $date,
    ): array {
        $marks = Attendance::query()
            ->where('class_key', $classKey)
            ->whereDate('attendance_date', $date)
            ->get()
            ->keyBy('student_id');

        return $attendance->studentsFor($teacher, $schoolYear, $classKey)
            ->map(function (User $student) use ($marks) {
                $mark = $marks->get($student->id);

                return [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'lrn' => $student->lrn,
                    'status' => $mark?->status,
                    'remarks' => $mark?->remarks,
                ];
            })
            ->values()
            ->all();
    }

    private function sheetDate(mixed $date): string
    {
        if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->today();
        }

        $parsed = strtotime($date);
        if ($parsed === false || $date > $this->today()) {
            return $this->today();
        }

        return $date;
    }

    private function today(): string
    {
        return now('Asia/Manila')->toDateString();
    }

    public function studentIndex(Request $request): Response
    {
        $student = $this->student();
        $schoolYear = SchoolSetting::currentSchoolYear();
        $date = $this->sheetDate($request->query('date'));
        $month = Carbon::parse($date);

        $marks = Attendance::query()
            ->where('student_id', $student->id)
            ->where('school_year', $schoolYear)
            ->with(['subject:id,name', 'section:id,name', 'teacher:id,first_name,last_name'])
            ->orderByDesc('attendance_date')
            ->orderBy('class_key')
            ->get();

        $mapMark = function (Attendance $mark): array {
            $teacherName = trim(($mark->teacher?->first_name ?? '').' '.($mark->teacher?->last_name ?? ''));

            return [
                'id' => $mark->id,
                'date' => $mark->attendance_date->toDateString(),
                'status' => $mark->status,
                'remarks' => $mark->remarks,
                'subject_name' => $mark->subject?->name ?? 'Advisory',
                'section_name' => $mark->section?->name,
                'teacher_name' => $teacherName !== '' ? $teacherName : null,
            ];
        };

        $records = $marks
            ->filter(fn (Attendance $mark) => $mark->attendance_date->toDateString() === $date)
            ->map($mapMark)
            ->values()
            ->all();

        $history = $marks
            ->filter(fn (Attendance $mark) => $mark->attendance_date->between(
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ))
            ->map($mapMark)
            ->values()
            ->all();

        return Inertia::render('Dashboard/Student/Attendance', [
            'user' => $student,
            'currentSchoolYear' => $schoolYear,
            'date' => $date,
            'today' => $this->today(),
            'records' => $records,
            'history' => $history,
            'summary' => [
                'present' => $marks->where('status', 'present')->count(),
                'absent' => $marks->where('status', 'absent')->count(),
                'late' => $marks->where('status', 'late')->count(),
                'excused' => $marks->where('status', 'excused')->count(),
            ],
        ]);
    }

    private function teacher(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    private function student(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
