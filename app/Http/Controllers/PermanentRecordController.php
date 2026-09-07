<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\StudentPermanentRecord;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PermanentRecordController extends Controller
{
    public function registrarIndex(Request $request): Response
    {
        return $this->index($request, 'registrar');
    }

    public function store(Request $request, User $student): RedirectResponse
    {
        $this->assertStaff();
        abort_unless($student->role === 'student', 404);

        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'school_year' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        $path = $file->store('permanent-records/'.$student->id, 'public');

        StudentPermanentRecord::create([
            'student_id' => $student->id,
            'school_year' => $validated['school_year'] ?: null,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'notes' => $validated['notes'] ?? null,
            'uploaded_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Old SP-10 / Form 137 file uploaded for '.$student->last_name.', '.$student->first_name.'.');
    }

    public function download(StudentPermanentRecord $record): StreamedResponse
    {
        $this->assertStaff();

        abort_unless(Storage::disk('public')->exists($record->file_path), 404);

        return Storage::disk('public')->download(
            $record->file_path,
            $record->original_filename,
        );
    }

    public function destroy(StudentPermanentRecord $record): RedirectResponse
    {
        $this->assertStaff();

        if ($record->file_path && Storage::disk('public')->exists($record->file_path)) {
            Storage::disk('public')->delete($record->file_path);
        }

        $record->delete();

        return back()->with('success', 'Uploaded SP-10 file removed.');
    }

    private function index(Request $request, string $viewer): Response
    {
        $settings = SchoolSetting::current();
        $schoolYear = $settings->current_school_year;
        $search = trim((string) $request->query('search', ''));
        $yearLevelId = $request->query('year_level_id');
        $hasFile = $request->query('has_file');

        $latestEnrollments = Enrollment::query()
            ->with(['yearLevel', 'section'])
            ->whereIn('status', ['enrolled', 'approved'])
            ->orderByDesc('school_year')
            ->orderByDesc('id')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $fileCounts = StudentPermanentRecord::query()
            ->selectRaw('student_id, count(*) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id');

        $gradedStudentIds = Grade::query()
            ->whereNotNull('final_grade')
            ->distinct()
            ->pluck('student_id');

        $filesByStudent = StudentPermanentRecord::query()
            ->with('uploadedBy:id,first_name,last_name')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('student_id');

        $students = User::query()
            ->where('role', 'student')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('middle_name', 'like', '%'.$search.'%')
                        ->orWhere('lrn', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get([
                'id',
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'lrn',
                'gender',
                'year_level_applying',
            ])
            ->map(function (User $student) use ($latestEnrollments, $fileCounts, $gradedStudentIds, $filesByStudent) {
                $enrollment = $latestEnrollments->get($student->id);
                $files = $filesByStudent->get($student->id, collect());

                return [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'suffix' => $student->suffix,
                    'lrn' => $student->lrn,
                    'gender' => $student->gender,
                    'year_level_id' => $enrollment?->year_level_id,
                    'year_level' => $enrollment?->yearLevel?->name ?? $student->year_level_applying,
                    'section' => $enrollment?->section?->name,
                    'school_year' => $enrollment?->school_year,
                    'has_grades' => $gradedStudentIds->contains($student->id),
                    'uploaded_count' => (int) ($fileCounts[$student->id] ?? 0),
                    'files' => $files->map(fn (StudentPermanentRecord $file) => [
                        'id' => $file->id,
                        'school_year' => $file->school_year,
                        'original_filename' => $file->original_filename,
                        'notes' => $file->notes,
                        'file_size' => $file->file_size,
                        'created_at' => $file->created_at?->format('M j, Y'),
                        'uploaded_by' => $file->uploadedBy
                            ? trim($file->uploadedBy->first_name.' '.$file->uploadedBy->last_name)
                            : null,
                    ])->values()->all(),
                ];
            })
            ->when($yearLevelId, function ($collection) use ($yearLevelId) {
                return $collection->filter(
                    fn (array $student) => (string) $student['year_level_id'] === (string) $yearLevelId
                )->values();
            })
            ->when($hasFile === '1', fn ($collection) => $collection->filter(fn (array $student) => $student['uploaded_count'] > 0)->values())
            ->when($hasFile === '0', fn ($collection) => $collection->filter(fn (array $student) => $student['uploaded_count'] === 0)->values())
            ->values();

        $schoolYears = Enrollment::query()
            ->select('school_year')
            ->whereNotNull('school_year')
            ->distinct()
            ->orderByDesc('school_year')
            ->pluck('school_year')
            ->values();

        if (! $schoolYears->contains($schoolYear)) {
            $schoolYears->prepend($schoolYear);
        }

        return Inertia::render('Dashboard/PermanentRecords/Index', [
            'user' => $request->user(),
            'viewer' => $viewer,
            'schoolYear' => $schoolYear,
            'filters' => [
                'search' => $search,
                'year_level_id' => $yearLevelId ? (string) $yearLevelId : '',
                'has_file' => in_array($hasFile, ['0', '1'], true) ? $hasFile : '',
            ],
            'yearLevels' => YearLevel::query()->ordered()->get(['id', 'name', 'code']),
            'schoolYears' => $schoolYears,
            'students' => $students,
            'totals' => [
                'students' => $students->count(),
                'with_files' => $students->where('uploaded_count', '>', 0)->count(),
                'with_grades' => $students->where('has_grades', true)->count(),
            ],
        ]);
    }

    private function assertStaff(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['administrator', 'registrar'], true), 403);
    }
}
