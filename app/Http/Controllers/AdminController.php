<?php

namespace App\Http\Controllers;

use App\Exceptions\StudentPromotionException;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\SectionSubjectTeacher;
use App\Models\StudentRequirement;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\SectionAssignmentService;
use App\Services\StudentPromotionService;
use App\Support\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // ==================== STUDENT MANAGEMENT ====================

    public function storeStudent(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'email' => 'required|email|unique:users,email',
            'phone_no' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'lrn' => ['required', 'digits:12', Rule::unique('users', 'lrn')],
            'guardian_full_name' => 'nullable|string|max:255',
            'guardian_contact_no' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'student';
        $validated['admission_status'] = 'approved';
        $validated['admission_reviewed_by'] = auth()->id();
        $validated['admission_reviewed_at'] = now();

        User::create($validated);

        return back()->with('success', 'Student created successfully.');
    }

    public function updateStudent(Request $request, User $student)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'email' => ['required', 'email', Rule::unique('users')->ignore($student->id)],
            'phone_no' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'lrn' => ['required', 'digits:12', Rule::unique('users', 'lrn')->ignore($student->id)],
            'guardian_full_name' => 'nullable|string|max:255',
            'guardian_contact_no' => 'nullable|string|max:20',
        ]);

        $student->update($validated);

        return back()->with('success', 'Student updated successfully.');
    }

    public function deleteStudent(User $student)
    {
        // Delete related enrollments first
        Enrollment::where('user_id', $student->id)->delete();
        $student->delete();

        return back()->with('success', 'Student deleted successfully.');
    }

    // ==================== TEACHER MANAGEMENT ====================

    public function storeTeacher(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'email' => 'required|email|unique:users,email',
            'phone_no' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'password' => 'required|string|min:8',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'teacher';
        $validated['admission_status'] = null;

        User::create($validated);

        return back()->with('success', 'Teacher created successfully.');
    }

    public function updateTeacher(Request $request, User $teacher)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'email' => ['required', 'email', Rule::unique('users')->ignore($teacher->id)],
            'phone_no' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
        ]);

        $teacher->update($validated);

        return back()->with('success', 'Teacher updated successfully.');
    }

    public function deleteTeacher(User $teacher)
    {
        // Remove adviser from sections
        Section::where('adviser_id', $teacher->id)->update(['adviser_id' => null]);
        $teacher->delete();

        return back()->with('success', 'Teacher deleted successfully.');
    }

    // ==================== ADMISSION MANAGEMENT ====================

    public function approveAdmission(Request $request, User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        $student->update([
            'admission_status' => 'approved',
            'admission_remarks' => $validated['remarks'] ?? null,
            'admission_reviewed_by' => auth()->id(),
            'admission_reviewed_at' => now(),
        ]);

        return back()->with('success', 'Admission approved. The student may now enroll.');
    }

    public function rejectAdmission(Request $request, User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }

        $validated = $request->validate([
            'remarks' => 'required|string|max:500',
        ]);

        $student->update([
            'admission_status' => 'rejected',
            'admission_remarks' => $validated['remarks'],
            'admission_reviewed_by' => auth()->id(),
            'admission_reviewed_at' => now(),
        ]);

        return back()->with('success', 'Admission rejected.');
    }

    // ==================== ENROLLMENT MANAGEMENT ====================

    public function approveEnrollment(Enrollment $enrollment)
    {
        if (! $this->markApproved($enrollment)) {
            return back()->withErrors([
                'enrollment' => 'Only pending enrollments can be approved.',
            ]);
        }

        return back()->with('success', 'Enrollment approved. Student is now ready for section assignment.');
    }

    public function approveSelected(Request $request)
    {
        $validated = $request->validate([
            'enrollment_ids' => 'required|array|min:1',
            'enrollment_ids.*' => 'integer|exists:enrollments,id',
        ]);

        $enrollments = Enrollment::query()
            ->whereIn('id', $validated['enrollment_ids'])
            ->where('school_year', SchoolSetting::currentSchoolYear())
            ->get();

        $approved = 0;
        $skipped = 0;

        DB::transaction(function () use ($enrollments, &$approved, &$skipped) {
            foreach ($enrollments as $enrollment) {
                if ($this->markApproved($enrollment)) {
                    $approved++;
                } else {
                    $skipped++;
                }
            }
        });

        if ($approved === 0) {
            return back()->withErrors([
                'enrollment' => 'None of the selected students could be approved. Only pending enrollments can be approved.',
            ]);
        }

        $message = "Approved {$approved} student(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} selected student(s) were skipped.";
        }

        return back()->with('success', $message);
    }

    public function rejectEnrollment(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        $enrollment->update([
            'status' => 'rejected',
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return back()->with('success', 'Enrollment rejected.');
    }

    public function assignSection(Request $request, Enrollment $enrollment)
    {
        if ($enrollment->section_id) {
            return back()->withErrors([
                'section_id' => 'This student already has a section. Use Transfer Section if they need to move.',
            ]);
        }

        $section = $this->validatedSectionForEnrollment($request, $enrollment);

        if (! $section) {
            return back()->withErrors([
                'section_id' => 'Choose a valid section for this student\'s year level.',
            ]);
        }

        $enrollment->update([
            'section_id' => $section->id,
        ]);

        return back()->with('success', 'Section assigned successfully.');
    }

    public function transferSection(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'confirm_transfer' => 'accepted',
        ], [
            'confirm_transfer.accepted' => 'Please confirm that this student really needs to transfer to another section.',
        ]);

        if (! $enrollment->section_id) {
            return back()->withErrors([
                'section_id' => 'This student is not assigned to a section yet. Use Assign Section instead.',
            ]);
        }

        $section = $this->validatedSectionForEnrollment($request, $enrollment);

        if (! $section) {
            return back()->withErrors([
                'section_id' => 'Choose a valid section for this student\'s year level.',
            ]);
        }

        if ((int) $enrollment->section_id === (int) $section->id) {
            return back()->withErrors([
                'section_id' => 'Choose a different section to transfer this student.',
            ]);
        }

        $enrollment->loadMissing('section');
        $fromSection = $enrollment->section?->name ?? 'their current section';

        $enrollment->update([
            'section_id' => $section->id,
        ]);

        return back()->with('success', "Student transferred from {$fromSection} to {$section->name}.");
    }

    private function validatedSectionForEnrollment(Request $request, Enrollment $enrollment): ?Section
    {
        if (! in_array($enrollment->status, ['approved', 'enrolled'], true)) {
            return null;
        }

        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        $section = Section::query()->find($validated['section_id']);

        if (! $section || (int) $section->year_level_id !== (int) $enrollment->year_level_id) {
            return null;
        }

        return $section;
    }

    public function enrollStudent(Enrollment $enrollment)
    {
        if (! $this->markEnrolled($enrollment)) {
            return back()->withErrors([
                'enrollment' => 'Only students with approved admission and approved enrollment can be enrolled.',
            ]);
        }

        return back()->with('success', 'Student enrolled successfully.');
    }

    public function enrollSelected(Request $request)
    {
        $validated = $request->validate([
            'enrollment_ids' => 'required|array|min:1',
            'enrollment_ids.*' => 'integer|exists:enrollments,id',
        ]);

        $enrollments = Enrollment::query()
            ->with('user')
            ->whereIn('id', $validated['enrollment_ids'])
            ->where('school_year', SchoolSetting::currentSchoolYear())
            ->get();

        $enrolled = 0;
        $skipped = 0;

        DB::transaction(function () use ($enrollments, &$enrolled, &$skipped) {
            foreach ($enrollments as $enrollment) {
                if ($this->markEnrolled($enrollment)) {
                    $enrolled++;
                } else {
                    $skipped++;
                }
            }
        });

        if ($enrolled === 0) {
            return back()->withErrors([
                'enrollment' => 'None of the selected students could be enrolled. Students must have approved admission and approved enrollment status.',
            ]);
        }

        $message = "Enrolled {$enrolled} student(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} selected student(s) were skipped.";
        }

        return back()->with('success', $message);
    }

    private function markApproved(Enrollment $enrollment): bool
    {
        if ($enrollment->status !== 'pending') {
            return false;
        }

        $enrollment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return true;
    }

    private function markEnrolled(Enrollment $enrollment): bool
    {
        if ($enrollment->user?->admission_status !== 'approved') {
            return false;
        }

        if ($enrollment->status !== 'approved') {
            return false;
        }

        $enrollment->update([
            'status' => 'enrolled',
            'enrolled_at' => now(),
            'approved_by' => $enrollment->approved_by ?? auth()->id(),
        ]);

        return true;
    }

    /**
     * Assign sections to approved students using GWA ranking, mixed ability, or shuffle.
     */
    public function autoAssignSections(Request $request, SectionAssignmentService $assignmentService)
    {
        $validated = $request->validate([
            'method' => 'required|in:gwa,mixed,shuffle',
            'year_level_id' => 'nullable|exists:year_levels,id',
            'gwa_source' => 'nullable|in:admission,teacher_grades',
        ]);

        $result = $assignmentService->assign(
            $validated['method'],
            isset($validated['year_level_id']) ? (int) $validated['year_level_id'] : null,
            SchoolSetting::currentSchoolYear(),
            $validated['gwa_source'] ?? 'admission',
        );

        if ($result['assigned'] === 0) {
            $message = $result['message'];
            if (! empty($result['warnings'])) {
                $message .= ' '.implode('; ', $result['warnings']);
            }

            return back()->withErrors([
                'assignment' => $message,
            ]);
        }

        $message = $result['message'];
        if (! empty($result['warnings'])) {
            $message .= ' Warnings: '.implode('; ', $result['warnings']);
        }

        return back()->with('success', $message);
    }

    public function reshuffleSectionsByGrades(Request $request, SectionAssignmentService $assignmentService)
    {
        $validated = $request->validate([
            'method' => 'required|in:gwa,mixed,shuffle',
            'year_level_id' => 'required|exists:year_levels,id',
            'preview' => 'nullable|boolean',
        ]);

        $preview = $request->boolean('preview');
        $result = $assignmentService->reshuffleByTeacherGrades(
            $validated['method'],
            (int) $validated['year_level_id'],
            SchoolSetting::currentSchoolYear(),
            ! $preview,
        );

        if ($preview) {
            return back()->with('reshufflePreview', $result);
        }

        if ($result['assigned'] === 0) {
            $message = $result['message'];
            if (! empty($result['warnings'])) {
                $message .= ' '.implode('; ', $result['warnings']);
            }

            return back()->withErrors([
                'assignment' => $message,
            ]);
        }

        $message = $result['message'];
        if (! empty($result['warnings'])) {
            $message .= ' Warnings: '.implode('; ', $result['warnings']);
        }

        return back()->with('success', $message);
    }

    public function dropEnrollment(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        if (! in_array($enrollment->status, ['approved', 'enrolled'], true)) {
            return back()->withErrors([
                'enrollment' => 'Only approved or enrolled students can be marked as drop-outs.',
            ]);
        }

        $note = trim((string) ($validated['remarks'] ?? ''));
        $existing = trim((string) $enrollment->remarks);
        $stamp = 'Marked as drop-out on '.now()->format('M d, Y').'.';
        $remarks = trim($existing === '' ? $stamp : $existing."\n".$stamp);
        if ($note !== '') {
            $remarks .= ' '.$note;
        }

        $enrollment->update([
            'status' => 'dropped',
            'remarks' => $remarks,
        ]);

        $enrollment->loadMissing('user');
        $name = trim(($enrollment->user?->first_name ?? '').' '.($enrollment->user?->last_name ?? 'Student'));

        return back()->with('success', $name.' was marked as a drop-out.');
    }

    // ==================== YEAR LEVEL PROMOTION ====================

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

    // ==================== YEAR LEVEL MANAGEMENT ====================

    public function storeYearLevel(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:year_levels,name',
            'code' => 'required|string|max:10|unique:year_levels,code',
            'description' => 'nullable|string|max:500',
            'level_type' => 'required|in:junior_high,senior_high',
            'is_active' => 'boolean',
        ]);

        YearLevel::create($validated);

        return back()->with('success', 'Year level created successfully.');
    }

    public function updateYearLevel(Request $request, YearLevel $yearLevel)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('year_levels')->ignore($yearLevel->id)],
            'code' => ['required', 'string', 'max:10', Rule::unique('year_levels')->ignore($yearLevel->id)],
            'description' => 'nullable|string|max:500',
            'level_type' => 'required|in:junior_high,senior_high',
            'is_active' => 'boolean',
        ]);

        $yearLevel->update($validated);

        return back()->with('success', 'Year level updated successfully.');
    }

    public function deleteYearLevel(YearLevel $yearLevel)
    {
        // Check if year level has enrollments
        if ($yearLevel->enrollments()->exists()) {
            return back()->with('error', 'Cannot delete year level with existing enrollments.');
        }

        $yearLevel->delete();

        return back()->with('success', 'Year level deleted successfully.');
    }

    // ==================== SECTION MANAGEMENT ====================

    public function storeSection(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'year_level_id' => 'required|exists:year_levels,id',
            'adviser_id' => 'nullable|exists:users,id',
            'capacity' => 'nullable|integer|min:1|max:100',
            'school_year' => ['required', 'string', 'max:20', function (string $attribute, mixed $value, $fail) {
                if (! SchoolYear::isValid((string) $value)) {
                    $fail('School year must be consecutive calendar years, e.g. 2026-2027.');
                }
            }],
            'is_active' => 'boolean',
        ]);

        $validated['school_year'] = SchoolYear::normalize($validated['school_year']);

        $duplicate = Section::query()
            ->where('year_level_id', $validated['year_level_id'])
            ->where('name', $validated['name'])
            ->where('school_year', $validated['school_year'])
            ->exists();

        if ($duplicate) {
            return back()->withErrors([
                'school_year' => 'This section already exists for school year '.$validated['school_year'].'.',
            ]);
        }

        Section::create($validated);

        return back()->with('success', 'Section created successfully.');
    }

    public function updateSection(Request $request, Section $section)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'year_level_id' => 'required|exists:year_levels,id',
            'adviser_id' => 'nullable|exists:users,id',
            'capacity' => 'nullable|integer|min:1|max:100',
            'is_active' => 'boolean',
        ]);

        $section->update($validated);

        return back()->with('success', 'Section updated successfully.');
    }

    public function deleteSection(Section $section)
    {
        // Check if section has enrollments
        if (Enrollment::where('section_id', $section->id)->exists()) {
            return back()->with('error', 'Cannot delete section with existing enrollments.');
        }

        $section->delete();

        return back()->with('success', 'Section deleted successfully.');
    }

    // ==================== SUBJECT MANAGEMENT ====================

    public function storeSubject(Request $request)
    {
        $validated = $this->validatedSubjectPayload($request);

        $subject = Subject::create(collect($validated)->except('section_ids')->all());
        $this->syncSubjectSections($subject, $validated);

        return back()->with('success', 'Subject created successfully.');
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $validated = $this->validatedSubjectPayload($request, $subject);

        $subject->update(collect($validated)->except('section_ids')->all());
        $this->syncSubjectSections($subject, $validated);

        return back()->with('success', 'Subject updated successfully.');
    }

    public function deleteSubject(Subject $subject)
    {
        $subject->delete();

        return back()->with('success', 'Subject deleted successfully.');
    }

    private function validatedSubjectPayload(Request $request, ?Subject $subject = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => $subject
                ? ['required', 'string', 'max:20', Rule::unique('subjects')->ignore($subject->id)]
                : 'required|string|max:20|unique:subjects,code',
            'description' => 'nullable|string|max:500',
            'year_level_id' => 'required|exists:year_levels,id',
            'subject_type' => 'required|in:core,specialized,applied,elective',
            'semester' => 'nullable|in:first,second,full_year',
            'is_active' => 'boolean',
            'section_ids' => 'nullable|array',
            'section_ids.*' => [
                'integer',
                Rule::exists('sections', 'id')->where('year_level_id', $request->input('year_level_id')),
            ],
        ]);
    }

    private function syncSubjectSections(Subject $subject, array $validated): void
    {
        $sectionIds = collect($validated['section_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $validIds = Section::query()
            ->where('year_level_id', $validated['year_level_id'])
            ->whereIn('id', $sectionIds)
            ->pluck('id');

        $subject->sections()->sync($validIds);
    }

    // ==================== RESET PASSWORD ====================

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password reset successfully.');
    }

    // ==================== TEACHER SUBJECT ASSIGNMENTS ====================
    // (What subjects a teacher CAN teach)

    public function storeTeacherSubject(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        // Check if teacher exists and is a teacher
        $teacher = User::where('id', $validated['teacher_id'])
            ->where('role', 'teacher')
            ->first();

        if (! $teacher) {
            return back()->with('error', 'Invalid teacher selected.');
        }

        // Check if assignment already exists
        $exists = TeacherSubject::where('teacher_id', $validated['teacher_id'])
            ->where('subject_id', $validated['subject_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'This teacher is already assigned to this subject.');
        }

        $takenByOther = TeacherSubject::where('subject_id', $validated['subject_id'])
            ->where('teacher_id', '!=', $validated['teacher_id'])
            ->with('teacher')
            ->first();

        if ($takenByOther) {
            $teacherName = trim(($takenByOther->teacher?->last_name ?? '').', '.($takenByOther->teacher?->first_name ?? ''), ' ,');

            return back()->withErrors([
                'subject_id' => $teacherName !== ''
                    ? "This subject is already assigned to {$teacherName}."
                    : 'This subject is already assigned to another teacher.',
            ]);
        }

        TeacherSubject::create($validated);

        return back()->with('success', 'Subject assigned to teacher successfully.');
    }

    public function deleteTeacherSubject(TeacherSubject $teacherSubject)
    {
        $teacherSubject->delete();

        return back()->with('success', 'Subject removed from teacher successfully.');
    }

    public function bulkAssignTeacherSubjects(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $teacher = User::where('id', $validated['teacher_id'])
            ->where('role', 'teacher')
            ->first();

        if (! $teacher) {
            return back()->with('error', 'Invalid teacher selected.');
        }

        $subjectIds = array_values(array_unique($validated['subject_ids'] ?? []));

        $takenSubjects = TeacherSubject::query()
            ->whereIn('subject_id', $subjectIds)
            ->where('teacher_id', '!=', $validated['teacher_id'])
            ->with(['subject', 'teacher'])
            ->get();

        if ($takenSubjects->isNotEmpty()) {
            $names = $takenSubjects
                ->map(function (TeacherSubject $row) {
                    $subjectName = $row->subject?->name ?: 'a subject';
                    $teacherName = trim(($row->teacher?->last_name ?? '').', '.($row->teacher?->first_name ?? ''), ' ,');

                    return $teacherName !== ''
                        ? "{$subjectName} ({$teacherName})"
                        : $subjectName;
                })
                ->unique()
                ->implode(', ');

            return back()->withErrors([
                'subject_ids' => "These subjects are already assigned to another teacher: {$names}.",
            ]);
        }

        TeacherSubject::where('teacher_id', $validated['teacher_id'])->delete();

        foreach ($subjectIds as $subjectId) {
            TeacherSubject::create([
                'teacher_id' => $validated['teacher_id'],
                'subject_id' => $subjectId,
            ]);
        }

        return back()->with('success', 'Teacher subjects updated successfully.');
    }

    // ==================== SECTION SUBJECT TEACHER ASSIGNMENTS ====================
    // (Which teacher teaches which subject in which section)

    public function storeSectionSubjectTeacher(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'school_year' => ['required', 'string', 'max:20', function (string $attribute, mixed $value, $fail) {
                if (! SchoolYear::isValid((string) $value)) {
                    $fail('School year must be consecutive calendar years, e.g. 2026-2027.');
                }
            }],
            'semester' => 'nullable|in:first,second,full_year',
            'schedule' => 'nullable|string|max:100',
            'room' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['school_year'] = SchoolYear::normalize($validated['school_year']);

        $teacherIds = TeacherSubject::where('subject_id', $validated['subject_id'])
            ->pluck('teacher_id');

        if ($teacherIds->isEmpty()) {
            return back()->withErrors(['subject_id' => 'This subject has no teacher yet. Assign it on the Teacher-Subject tab first.']);
        }

        if ($teacherIds->count() > 1) {
            return back()->withErrors(['subject_id' => 'More than one teacher is assigned to this subject. Keep only one teacher on the Teacher-Subject tab.']);
        }

        $validated['teacher_id'] = $teacherIds->first();

        // Check if assignment already exists
        $exists = SectionSubjectTeacher::where('section_id', $validated['section_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('school_year', $validated['school_year'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['subject_id' => 'A teacher is already assigned to this subject for this section.']);
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        SectionSubjectTeacher::create($validated);

        return back()->with('success', 'Teacher assigned to section-subject successfully.');
    }

    public function updateSectionSubjectTeacher(Request $request, SectionSubjectTeacher $assignment)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'schedule' => 'nullable|string|max:100',
            'room' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        // Verify teacher can teach this subject
        $canTeach = TeacherSubject::where('teacher_id', $validated['teacher_id'])
            ->where('subject_id', $assignment->subject_id)
            ->exists();

        if (! $canTeach) {
            return back()->with('error', 'This teacher is not assigned to teach this subject.');
        }

        $assignment->update($validated);

        return back()->with('success', 'Assignment updated successfully.');
    }

    public function deleteSectionSubjectTeacher(SectionSubjectTeacher $assignment)
    {
        $assignment->delete();

        return back()->with('success', 'Teacher assignment removed successfully.');
    }

    public function getTeachersForSubject(Subject $subject)
    {
        $teachers = User::where('role', 'teacher')
            ->whereHas('teachableSubjects', function ($query) use ($subject) {
                $query->where('subjects.id', $subject->id);
            })
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix']);

        return response()->json($teachers);
    }

    // ==================== STUDENT REQUIREMENTS MANAGEMENT ====================

    public function verifyRequirement(StudentRequirement $requirement)
    {
        $requirement->update([
            'status' => 'verified',
            'remarks' => null,
        ]);

        return back()->with('success', 'Requirement verified successfully.');
    }

    public function rejectRequirement(Request $request, StudentRequirement $requirement)
    {
        $validated = $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        $requirement->update([
            'status' => 'rejected',
            'remarks' => $validated['remarks'],
        ]);

        return back()->with('success', 'Requirement rejected.');
    }

    // ==================== SCHOOL SETTINGS ====================

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'school_id' => 'nullable|string|max:50',
            'region' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'school_head' => 'required|string|max:255',
            'current_school_year' => ['required', 'string', 'max:20', function (string $attribute, mixed $value, $fail) {
                if (! SchoolYear::isValid((string) $value)) {
                    $fail('School year must be consecutive calendar years, e.g. 2026-2027.');
                }
            }],
            'current_term' => 'required|integer|in:1,2,3',
            'enrollment_open' => 'required|boolean',
        ]);

        $validated['current_school_year'] = SchoolYear::normalize($validated['current_school_year']);

        if (! AcademicYear::query()->where('year', $validated['current_school_year'])->exists()) {
            return back()->withErrors([
                'current_school_year' => 'Add that school year first before setting it as current.',
            ]);
        }

        SchoolSetting::current()->update($validated);

        return back()->with('success', 'Settings saved successfully.');
    }

    public function storeAcademicYear(Request $request)
    {
        $validated = $request->validate([
            'start_year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $year = SchoolYear::normalize((string) $validated['start_year']);
        if (! $year) {
            return back()->withErrors([
                'start_year' => 'School year must be consecutive calendar years, e.g. 2026-2027.',
            ]);
        }

        if (AcademicYear::query()->where('year', $year)->exists()) {
            return back()->withErrors([
                'start_year' => 'School year '.$year.' is already set up.',
            ]);
        }

        AcademicYear::create(['year' => $year]);

        $settings = SchoolSetting::current();
        if (! $settings->current_school_year) {
            $settings->update(['current_school_year' => $year]);
        }

        return back()->with('success', 'School year '.$year.' was added.');
    }

    public function destroyAcademicYear(AcademicYear $academicYear)
    {
        if ($academicYear->year === SchoolSetting::currentSchoolYear()) {
            return back()->with('error', 'You cannot remove the current school year.');
        }

        if ($academicYear->isInUse()) {
            return back()->with('error', 'You cannot remove '.$academicYear->year.' because it already has records.');
        }

        $academicYear->delete();

        return back()->with('success', 'School year '.$academicYear->year.' was removed.');
    }

    public function updateAdminPassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
