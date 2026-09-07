<?php

namespace App\Http\Controllers;

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
use App\Services\StudentPromotionService;
use App\Support\AdmissionDocuments;
use App\Support\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function administrator()
    {
        $currentSchoolYear = $this->getCurrentSchoolYear();

        // Get statistics
        $totalStudents = User::where('role', 'student')->count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalEnrolled = Enrollment::where('school_year', $currentSchoolYear)
            ->where('status', 'enrolled')
            ->count();
        $pendingEnrollments = Enrollment::where('school_year', $currentSchoolYear)
            ->where('status', 'pending')
            ->count();
        $totalSubjects = Subject::count();
        $totalSections = Section::where('school_year', $currentSchoolYear)->count();

        // Get recent enrollments
        $recentEnrollments = Enrollment::with(['user', 'yearLevel', 'section'])
            ->where('school_year', $currentSchoolYear)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get enrollment by year level
        $enrollmentByYearLevel = YearLevel::withCount(['enrollments' => function ($query) use ($currentSchoolYear) {
            $query->where('school_year', $currentSchoolYear)
                ->where('status', 'enrolled');
        }])
            ->ordered()
            ->get();

        // Get all data for management
        $students = User::where('role', 'student')
            ->orderBy('last_name')
            ->get();

        $teachers = User::where('role', 'teacher')
            ->orderBy('last_name')
            ->get();

        $yearLevels = YearLevel::withCount(['sections' => function ($query) use ($currentSchoolYear) {
            $query->where('school_year', $currentSchoolYear);
        }, 'subjects'])
            ->ordered()
            ->get();

        $sections = Section::with(['yearLevel', 'adviser'])
            ->where('school_year', $currentSchoolYear)
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get();

        $subjects = Subject::with('yearLevel')
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get();

        $enrollments = Enrollment::with(['user', 'yearLevel', 'section', 'approvedBy'])
            ->where('school_year', $currentSchoolYear)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get teacher-subject assignments (what subjects each teacher can teach)
        $teacherSubjects = TeacherSubject::with(['teacher', 'subject.yearLevel'])
            ->get();

        // Get section-subject-teacher assignments (which teacher teaches what subject in which section)
        $sectionSubjectTeachers = SectionSubjectTeacher::with(['section.yearLevel', 'subject', 'teacher'])
            ->where('school_year', $currentSchoolYear)
            ->orderBy('section_id')
            ->get();

        // Get teachers with their teachable subjects for easy lookup
        $teachersWithSubjects = User::where('role', 'teacher')
            ->with(['teachableSubjects'])
            ->orderBy('last_name')
            ->get();

        // Get all student requirements with user info
        $studentRequirements = StudentRequirement::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Dashboard/Administrator', [
            'user' => Auth::user(),
            'stats' => [
                'totalStudents' => $totalStudents,
                'totalTeachers' => $totalTeachers,
                'totalEnrolled' => $totalEnrolled,
                'pendingEnrollments' => $pendingEnrollments,
                'totalSubjects' => $totalSubjects,
                'totalSections' => $totalSections,
            ],
            'recentEnrollments' => $recentEnrollments,
            'enrollmentByYearLevel' => $enrollmentByYearLevel,
            'students' => $students,
            'teachers' => $teachers,
            'teachersWithSubjects' => $teachersWithSubjects,
            'yearLevels' => $yearLevels,
            'sections' => $sections,
            'subjects' => $subjects,
            'enrollments' => $enrollments,
            'teacherSubjects' => $teacherSubjects,
            'sectionSubjectTeachers' => $sectionSubjectTeachers,
            'studentRequirements' => $studentRequirements,
            'currentSchoolYear' => $currentSchoolYear,
        ]);
    }

    /**
     * Admin Dashboard Page
     */
    public function adminDashboard()
    {
        $currentSchoolYear = $this->getCurrentSchoolYear();

        $stats = [
            'totalStudents' => User::where('role', 'student')->count(),
            'totalTeachers' => User::where('role', 'teacher')->count(),
            'totalEnrolled' => Enrollment::where('school_year', $currentSchoolYear)->where('status', 'enrolled')->count(),
            'pendingEnrollments' => Enrollment::where('school_year', $currentSchoolYear)->where('status', 'pending')->count(),
            'pendingAdmissions' => User::where('role', 'student')->where('admission_status', 'pending')->count(),
            'totalSubjects' => Subject::count(),
            'totalSections' => Section::where('school_year', $currentSchoolYear)->count(),
        ];

        $recentEnrollments = Enrollment::with(['user', 'yearLevel', 'section'])
            ->where('school_year', $currentSchoolYear)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $enrollmentByYearLevel = YearLevel::withCount(['enrollments' => function ($query) use ($currentSchoolYear) {
            $query->where('school_year', $currentSchoolYear)->where('status', 'enrolled');
        }])->ordered()->get();

        return Inertia::render('Dashboard/Admin/Index', [
            'user' => Auth::user(),
            'stats' => $stats,
            'recentEnrollments' => $recentEnrollments,
            'enrollmentByYearLevel' => $enrollmentByYearLevel,
            'currentSchoolYear' => $currentSchoolYear,
        ]);
    }

    /**
     * Admin Students Management Page
     */
    public function adminStudents(StudentPromotionService $promotionService)
    {
        $currentSchoolYear = $this->getCurrentSchoolYear();

        $students = User::where('role', 'student')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $this->attachCurrentEnrollments($students, $currentSchoolYear);

        $promotionService->attachSummaries($students, $currentSchoolYear);

        $sectionYears = $students
            ->map(fn (User $student) => $student->currentEnrollment?->school_year)
            ->filter()
            ->push($currentSchoolYear)
            ->unique()
            ->values();

        $sections = Section::with('yearLevel')
            ->whereIn('school_year', $sectionYears)
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get();

        $yearLevels = YearLevel::ordered()->get();

        return Inertia::render('Dashboard/Admin/Students', [
            'user' => Auth::user(),
            'students' => $students,
            'yearLevels' => $yearLevels,
            'sections' => $sections,
            'currentSchoolYear' => $currentSchoolYear,
        ]);
    }

    /**
     * Admin Teachers Management Page
     */
    public function adminTeachers()
    {
        $teachers = User::where('role', 'teacher')
            ->with(['teachableSubjects'])
            ->orderBy('last_name')
            ->get();

        $subjects = Subject::with('yearLevel')
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('Dashboard/Admin/Teachers', [
            'user' => Auth::user(),
            'teachers' => $teachers,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Admin Enrollments Management Page
     */
    public function adminEnrollments()
    {
        $currentSchoolYear = $this->getCurrentSchoolYear();

        $enrollments = Enrollment::with(['user', 'yearLevel', 'section', 'approvedBy'])
            ->where('school_year', $currentSchoolYear)
            ->orderBy('created_at', 'desc')
            ->get();

        $teacherGwas = Grade::query()
            ->where('school_year', $currentSchoolYear)
            ->whereNotNull('final_grade')
            ->selectRaw('student_id, ROUND(AVG(final_grade), 2) as gwa')
            ->groupBy('student_id')
            ->pluck('gwa', 'student_id');

        $enrollments->each(function (Enrollment $enrollment) use ($teacherGwas) {
            $enrollment->setAttribute('teacher_gwa', $teacherGwas->get($enrollment->user_id));
        });

        $sections = Section::with(['yearLevel'])
            ->where('school_year', $currentSchoolYear)
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get();

        $yearLevels = YearLevel::ordered()->get();

        return Inertia::render('Dashboard/Admin/Enrollments', [
            'user' => Auth::user(),
            'enrollments' => $enrollments,
            'sections' => $sections,
            'yearLevels' => $yearLevels,
            'currentSchoolYear' => $currentSchoolYear,
        ]);
    }

    /**
     * Admin Admissions Management Page
     */
    public function adminAdmissions()
    {
        $applicants = User::where('role', 'student')
            ->with(['admissionReviewedBy:id,first_name,last_name'])
            ->orderByRaw("CASE admission_status WHEN 'pending' THEN 1 WHEN 'approved' THEN 2 ELSE 3 END")
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Dashboard/Admin/Admissions', [
            'user' => Auth::user(),
            'applicants' => $applicants,
        ]);
    }

    /**
     * Admin Sections Management Page
     */
    public function adminSections(Request $request)
    {
        $currentSchoolYear = $this->getCurrentSchoolYear();
        $availableSchoolYears = $this->availableSectionSchoolYears($currentSchoolYear);
        $schoolYear = $this->resolveSectionSchoolYear(
            (string) $request->query('sy', ''),
            $currentSchoolYear,
            $availableSchoolYears,
        );

        $sections = Section::with(['yearLevel', 'adviser'])
            ->withCount(['enrollments' => function ($query) {
                $query->where('status', 'enrolled');
            }])
            ->where('school_year', $schoolYear)
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get();

        $yearLevels = YearLevel::ordered()->get();

        $teachers = User::where('role', 'teacher')
            ->orderBy('last_name')
            ->get();

        return Inertia::render('Dashboard/Admin/Sections', [
            'user' => Auth::user(),
            'sections' => $sections,
            'yearLevels' => $yearLevels,
            'teachers' => $teachers,
            'currentSchoolYear' => $currentSchoolYear,
            'selectedSchoolYear' => $schoolYear,
            'availableSchoolYears' => $availableSchoolYears,
            'officialSchoolYears' => SchoolYear::selectable()->all(),
        ]);
    }

    /**
     * Admin Subjects Management Page
     */
    public function adminSubjects()
    {
        $subjects = Subject::with('yearLevel')
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get();

        $yearLevels = YearLevel::ordered()->get();

        return Inertia::render('Dashboard/Admin/Subjects', [
            'user' => Auth::user(),
            'subjects' => $subjects,
            'yearLevels' => $yearLevels,
        ]);
    }

    /**
     * Admin Year Levels Management Page
     */
    public function adminYearLevels()
    {
        $currentSchoolYear = $this->getCurrentSchoolYear();

        $yearLevels = YearLevel::withCount(['sections' => function ($query) use ($currentSchoolYear) {
            $query->where('school_year', $currentSchoolYear);
        }, 'subjects', 'enrollments' => function ($query) use ($currentSchoolYear) {
            $query->where('school_year', $currentSchoolYear)->where('status', 'enrolled');
        }])->ordered()->get();

        return Inertia::render('Dashboard/Admin/YearLevels', [
            'user' => Auth::user(),
            'yearLevels' => $yearLevels,
            'currentSchoolYear' => $currentSchoolYear,
        ]);
    }

    /**
     * Admin Teacher Assignments Page
     */
    public function adminTeacherAssignments()
    {
        $currentSchoolYear = $this->getCurrentSchoolYear();

        $teacherSubjects = TeacherSubject::with(['teacher', 'subject.yearLevel'])
            ->get();

        $sectionSubjectTeachers = SectionSubjectTeacher::with(['section.yearLevel', 'subject', 'teacher'])
            ->where('school_year', $currentSchoolYear)
            ->orderBy('section_id')
            ->get();

        // Get teachers with their teachable subjects - map to 'subjects' for frontend
        $teachersWithSubjects = User::where('role', 'teacher')
            ->with(['teachableSubjects.yearLevel'])
            ->orderBy('last_name')
            ->get()
            ->map(function ($teacher) {
                $teacherArray = $teacher->toArray();
                $teacherArray['subjects'] = $teacher->teachableSubjects;

                return $teacherArray;
            });

        $subjects = Subject::with('yearLevel')
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get();

        $sections = Section::with(['yearLevel'])
            ->where('school_year', $currentSchoolYear)
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get();

        $yearLevels = YearLevel::ordered()->get();

        return Inertia::render('Dashboard/Admin/TeacherAssignments', [
            'user' => Auth::user(),
            'teacherSubjects' => $teacherSubjects,
            'sectionSubjectTeachers' => $sectionSubjectTeachers,
            'teachersWithSubjects' => $teachersWithSubjects,
            'teachers' => $teachersWithSubjects,
            'subjects' => $subjects,
            'sections' => $sections,
            'yearLevels' => $yearLevels,
            'currentSchoolYear' => $currentSchoolYear,
        ]);
    }

    /**
     * Admin Requirements Management Page
     */
    public function adminRequirements()
    {
        // Get all requirements grouped by user
        $requirements = StudentRequirement::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group requirements by user_id
        $studentRequirements = $requirements->groupBy('user_id')->map(function ($userRequirements) {
            $firstReq = $userRequirements->first();

            return [
                'user_id' => $firstReq->user_id,
                'user' => $firstReq->user,
                'requirements' => $userRequirements->map(function ($req) {
                    return [
                        'id' => $req->id,
                        'requirement_type' => $req->requirement_type,
                        'status' => $req->status,
                        'file_path' => $req->file_path,
                        'original_filename' => $req->original_filename,
                        'remarks' => $req->remarks,
                        'created_at' => $req->created_at,
                        'updated_at' => $req->updated_at,
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        return Inertia::render('Dashboard/Admin/Requirements', [
            'user' => Auth::user(),
            'studentRequirements' => $studentRequirements,
        ]);
    }

    /**
     * Admin Settings Page
     */
    public function adminSettings()
    {
        $current = SchoolSetting::currentSchoolYear();
        if ($current && ! AcademicYear::query()->where('year', $current)->exists()) {
            AcademicYear::create(['year' => $current]);
        }

        $usedYears = collect()
            ->merge(Section::query()->whereNotNull('school_year')->pluck('school_year'))
            ->merge(Enrollment::query()->whereNotNull('school_year')->pluck('school_year'))
            ->merge(Grade::query()->whereNotNull('school_year')->pluck('school_year'))
            ->merge(SectionSubjectTeacher::query()->whereNotNull('school_year')->pluck('school_year'))
            ->map(fn ($year) => SchoolYear::normalize((string) $year))
            ->filter()
            ->unique();

        $academicYears = AcademicYear::query()
            ->orderByDesc('year')
            ->get()
            ->map(fn (AcademicYear $year) => [
                'id' => $year->id,
                'year' => $year->year,
                'is_current' => $year->year === $current,
                'can_remove' => $year->year !== $current && ! $usedYears->contains($year->year),
            ])
            ->values();

        return Inertia::render('Dashboard/Admin/Settings', [
            'user' => Auth::user(),
            'settings' => SchoolSetting::current(),
            'academicYears' => $academicYears,
            'officialSchoolYears' => $academicYears->pluck('year')->all(),
        ]);
    }

    public function teacher()
    {
        $user = $this->authenticatedUser();

        // Get advisory section for this teacher
        $advisorySection = Section::where('adviser_id', $user->id)
            ->with('yearLevel')
            ->first();

        // Get enrolled students in advisory section
        $advisoryStudents = $advisorySection
            ? Enrollment::where('section_id', $advisorySection->id)
                ->where('status', 'enrolled')
                ->with('user')
                ->get()
                ->map(fn ($enrollment) => $enrollment->user)
            : collect();

        $currentSchoolYear = $this->getCurrentSchoolYear();
        $teachableSubjects = $user->teachableSubjects()->with('yearLevel')->get();
        $studentGrades = $this->teacherGradeRows($user, $teachableSubjects, $currentSchoolYear);

        // The Subjects Handled card and grade filters should only reflect
        // what this teacher is actually teaching in the admin-set school
        // year — not every subject they *could* teach.
        $teacherSubjects = $studentGrades
            ->pluck('subject')
            ->filter()
            ->unique(fn ($subject) => $subject->id)
            ->values();

        $teacherSections = $studentGrades
            ->pluck('section')
            ->filter()
            ->unique(fn ($section) => $section->id)
            ->values();

        return Inertia::render('Dashboard/Teacher', [
            'user' => $user,
            'advisorySection' => $advisorySection,
            'advisoryStudents' => $advisoryStudents,
            'teacherSubjects' => $teacherSubjects,
            'teacherSections' => $teacherSections,
            'studentGrades' => $studentGrades,
            'currentSchoolYear' => $currentSchoolYear,
            'currentTerm' => SchoolSetting::current()->current_term,
            'schoolHead' => SchoolSetting::current()->school_head,
        ]);
    }

    public function teacherSubjectStudents(Subject $subject)
    {
        $teacher = $this->authenticatedUser();
        $this->assertTeacherHandlesSubject($teacher, $subject);

        $schoolYear = $this->getCurrentSchoolYear();
        $subject->load('yearLevel');

        return Inertia::render('Dashboard/Teacher/SubjectStudents', [
            'user' => $teacher,
            'subject' => $subject,
            'students' => $this->studentsForTeacherSubject($teacher, $subject, $schoolYear),
            'currentSchoolYear' => $schoolYear,
        ]);
    }

    public function student()
    {
        $user = $this->authenticatedUser();

        // Get or create student requirements
        $requirementTypes = AdmissionDocuments::typesForYearLevel($user->year_level_applying);

        foreach ($requirementTypes as $type) {
            StudentRequirement::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'requirement_type' => $type,
                ]
            );
        }

        $catalog = AdmissionDocuments::catalog();
        $requirements = StudentRequirement::where('user_id', $user->id)
            ->whereIn('requirement_type', $requirementTypes)
            ->get()
            ->map(function ($req) use ($catalog) {
                $meta = $catalog[$req->requirement_type] ?? null;

                return [
                    'id' => $req->id,
                    'type' => $req->requirement_type,
                    'name' => $meta['label'] ?? $req->requirement_type,
                    'description' => $meta['description'] ?? '',
                    'submitted' => $req->status === 'submitted' || $req->status === 'verified',
                    'file_name' => $req->original_filename,
                    'submitted_date' => $req->updated_at?->format('M d, Y'),
                    'status' => $req->status,
                ];
            });

        // Get enrollment data
        $currentSchoolYear = $this->getCurrentSchoolYear();

        $currentEnrollment = Enrollment::where('user_id', $user->id)
            ->where('school_year', $currentSchoolYear)
            ->with(['yearLevel', 'section', 'section.adviser'])
            ->first();

        $yearLevels = YearLevel::active()->ordered()->get();

        $enrollmentHistory = Enrollment::where('user_id', $user->id)
            ->with(['yearLevel', 'section'])
            ->orderBy('school_year', 'desc')
            ->get();

        // Get enrolled subjects based on current enrollment
        $enrolledSubjects = [];
        $schedule = [];

        if ($currentEnrollment && $currentEnrollment->status === 'enrolled') {
            $enrolledSubjects = Subject::where('year_level_id', $currentEnrollment->year_level_id)
                ->active()
                ->orderBy('subject_type')
                ->orderBy('name')
                ->get()
                ->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'code' => $subject->code,
                        'name' => $subject->name,
                        'description' => $subject->description,
                        'type' => $subject->subject_type,
                        'units' => $subject->units,
                        'hours_per_week' => $subject->hours_per_week,
                        'semester' => $subject->semester,
                    ];
                });

            // Get schedule from section_subject_teachers if student has a section
            if ($currentEnrollment->section_id) {
                $schedule = SectionSubjectTeacher::where('section_id', $currentEnrollment->section_id)
                    ->where('school_year', $currentSchoolYear)
                    ->where('is_active', true)
                    ->with(['subject', 'teacher'])
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->id,
                            'subject_code' => $assignment->subject->code,
                            'subject_name' => $assignment->subject->name,
                            'teacher_name' => $assignment->teacher
                                ? $assignment->teacher->first_name.' '.$assignment->teacher->last_name
                                : 'TBA',
                            'schedule' => $assignment->schedule ?? 'TBA',
                            'room' => $assignment->room ?? 'TBA',
                            'semester' => $assignment->semester,
                        ];
                    });
            }
        }

        return Inertia::render('Dashboard/Student', [
            'user' => $user,
            'requirements' => $requirements,
            'enrollment' => [
                'current' => $currentEnrollment,
                'yearLevels' => $yearLevels,
                'history' => $enrollmentHistory,
                'currentSchoolYear' => $currentSchoolYear,
            ],
            'subjects' => $enrolledSubjects,
            'schedule' => $schedule,
        ]);
    }

    /**
     * Student Dashboard Page (Index)
     */
    public function studentDashboard()
    {
        $user = $this->authenticatedUser();
        $currentSchoolYear = $this->getCurrentSchoolYear();

        $currentEnrollment = Enrollment::where('user_id', $user->id)
            ->where('school_year', $currentSchoolYear)
            ->with(['yearLevel', 'section', 'section.adviser'])
            ->first();

        $enrolledSubjects = [];
        $schedule = [];

        if ($currentEnrollment && $currentEnrollment->status === 'enrolled') {
            $enrolledSubjects = Subject::where('year_level_id', $currentEnrollment->year_level_id)
                ->active()
                ->orderBy('subject_type')
                ->orderBy('name')
                ->get();

            if ($currentEnrollment->section_id) {
                $schedule = SectionSubjectTeacher::where('section_id', $currentEnrollment->section_id)
                    ->where('school_year', $currentSchoolYear)
                    ->where('is_active', true)
                    ->with(['subject', 'teacher'])
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->id,
                            'subject_code' => $assignment->subject->code,
                            'subject_name' => $assignment->subject->name,
                            'teacher_name' => $assignment->teacher
                                ? $assignment->teacher->first_name.' '.$assignment->teacher->last_name
                                : 'TBA',
                            'schedule' => $assignment->schedule ?? 'TBA',
                            'room' => $assignment->room ?? 'TBA',
                            'semester' => $assignment->semester,
                        ];
                    });
            }
        }

        return Inertia::render('Dashboard/Student/Index', [
            'user' => $user,
            'enrollment' => [
                'current' => $currentEnrollment,
                'currentSchoolYear' => $currentSchoolYear,
            ],
            'subjects' => $enrolledSubjects,
            'schedule' => $schedule,
            'gwaProgress' => $this->getStudentGwaProgress($user),
        ]);
    }

    /**
     * Build GWA chart points starting from previous GWA, then yearly averages.
     */
    private function getStudentGwaProgress($user): array
    {
        $points = [];

        $previousGwa = $user->previous_gwa;
        if ($previousGwa === null || $previousGwa === '') {
            $firstEnrollment = Enrollment::where('user_id', $user->id)
                ->whereNotNull('previous_gwa')
                ->orderBy('school_year')
                ->first();
            $previousGwa = $firstEnrollment?->previous_gwa;
        }

        if ($previousGwa !== null && $previousGwa !== '') {
            $points[] = [
                'label' => 'Previous GWA',
                'gwa' => round((float) $previousGwa, 2),
            ];
        }

        $enrollments = Enrollment::where('user_id', $user->id)
            ->whereIn('status', ['enrolled', 'approved', 'pending'])
            ->with('yearLevel')
            ->orderBy('school_year')
            ->get();

        foreach ($enrollments as $enrollment) {
            $average = Grade::where('student_id', $user->id)
                ->where('school_year', $enrollment->school_year)
                ->whereNotNull('final_grade')
                ->avg('final_grade');

            if ($average === null) {
                continue;
            }

            $yearLevelName = $enrollment->yearLevel?->name ?: 'Enrollment';
            $points[] = [
                'label' => $yearLevelName.' ('.$enrollment->school_year.')',
                'gwa' => round((float) $average, 2),
            ];
        }

        return $points;
    }

    /**
     * Student Profile Page
     */
    public function studentProfile()
    {
        return Inertia::render('Dashboard/Student/Profile', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Student Enrollment Page
     */
    public function studentEnrollment()
    {
        $user = $this->authenticatedUser();
        $currentSchoolYear = $this->getCurrentSchoolYear();

        $currentEnrollment = Enrollment::where('user_id', $user->id)
            ->where('school_year', $currentSchoolYear)
            ->with(['yearLevel', 'section', 'section.adviser'])
            ->first();

        $yearLevels = YearLevel::active()->ordered()->get();

        $enrollmentHistory = Enrollment::where('user_id', $user->id)
            ->with(['yearLevel', 'section'])
            ->orderBy('school_year', 'desc')
            ->get();

        $previousYearLevel = $user->previousYearLevel($currentSchoolYear);

        return Inertia::render('Dashboard/Student/Enrollment', [
            'user' => $user,
            'enrollment' => [
                'current' => $currentEnrollment,
                'yearLevels' => $yearLevels,
                'history' => $enrollmentHistory,
                'currentSchoolYear' => $currentSchoolYear,
                'enrollmentOpen' => SchoolSetting::enrollmentOpen(),
                'previousYearLevel' => $previousYearLevel,
            ],
        ]);
    }

    /**
     * Student Subjects Page
     */
    public function studentSubjects()
    {
        $user = $this->authenticatedUser();
        $currentSchoolYear = $this->getCurrentSchoolYear();

        $currentEnrollment = Enrollment::where('user_id', $user->id)
            ->where('school_year', $currentSchoolYear)
            ->with(['yearLevel', 'section'])
            ->first();

        $enrolledSubjects = [];

        if ($currentEnrollment && $currentEnrollment->status === 'enrolled') {
            $enrolledSubjects = Subject::where('year_level_id', $currentEnrollment->year_level_id)
                ->active()
                ->orderBy('subject_type')
                ->orderBy('name')
                ->get()
                ->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'code' => $subject->code,
                        'name' => $subject->name,
                        'description' => $subject->description,
                        'type' => $subject->subject_type,
                        'units' => $subject->units,
                        'hours_per_week' => $subject->hours_per_week,
                        'semester' => $subject->semester,
                    ];
                });
        }

        return Inertia::render('Dashboard/Student/Subjects', [
            'user' => $user,
            'enrollment' => [
                'current' => $currentEnrollment,
                'currentSchoolYear' => $currentSchoolYear,
            ],
            'subjects' => $enrolledSubjects,
        ]);
    }

    /**
     * Student Grades Page
     */
    public function studentGrades()
    {
        $user = $this->authenticatedUser();
        $currentSchoolYear = $this->getCurrentSchoolYear();

        // Get current enrollment
        $currentEnrollment = Enrollment::where('user_id', $user->id)
            ->where('school_year', $currentSchoolYear)
            ->with(['yearLevel', 'section'])
            ->first();

        $grades = collect();

        if ($currentEnrollment && $currentEnrollment->status === 'enrolled') {
            // Get all subjects for the student's year level
            $subjects = Subject::where('year_level_id', $currentEnrollment->year_level_id)
                ->active()
                ->orderBy('subject_type')
                ->orderBy('name')
                ->get();

            // Get all grades for the student
            $studentGrades = Grade::where('student_id', $user->id)
                ->where('school_year', $currentSchoolYear)
                ->get()
                ->keyBy('subject_id');

            // Map all subjects with their grades (if they exist)
            $grades = $subjects->map(function ($subject) use ($studentGrades) {
                $grade = $studentGrades->get($subject->id);

                return [
                    'id' => $grade ? $grade->id : null,
                    'subject_id' => $subject->id,
                    'subject_code' => $subject->code,
                    'subject_name' => $subject->name,
                    'term_1' => $grade ? $grade->term_1 : null,
                    'term_2' => $grade ? $grade->term_2 : null,
                    'term_3' => $grade ? $grade->term_3 : null,
                    'final_grade' => $grade ? $grade->final_grade : null,
                ];
            });
        }

        return Inertia::render('Dashboard/Student/Grades', [
            'user' => $user,
            'grades' => $grades,
            'enrollment' => $currentEnrollment,
        ]);
    }

    /**
     * Student Requirements Page
     */
    public function studentRequirements()
    {
        $user = $this->authenticatedUser();

        // Get or create student requirements
        $requirementTypes = AdmissionDocuments::typesForYearLevel($user->year_level_applying);
        $catalog = AdmissionDocuments::catalog();

        foreach ($requirementTypes as $type) {
            StudentRequirement::firstOrCreate([
                'user_id' => $user->id,
                'requirement_type' => $type,
            ]);
        }

        $requirements = StudentRequirement::where('user_id', $user->id)
            ->whereIn('requirement_type', $requirementTypes)
            ->get()
            ->map(function ($req) use ($catalog) {
                $meta = $catalog[$req->requirement_type] ?? null;

                return [
                    'id' => $req->id,
                    'name' => $meta['label'] ?? $req->requirement_type,
                    'description' => $meta['description'] ?? '',
                    'type' => $req->requirement_type,
                    'submitted' => $req->status === 'submitted' || $req->status === 'verified',
                    'file_name' => $req->original_filename,
                    'submitted_date' => $req->updated_at?->format('M d, Y'),
                    'status' => $req->status,
                ];
            });

        return Inertia::render('Dashboard/Student/Requirements', [
            'user' => $user,
            'requirements' => $requirements,
        ]);
    }

    /**
     * Update student grades
     */
    public function updateGrade(Request $request, $studentId, $subjectId)
    {
        $request->validate([
            'term_1' => 'nullable|numeric|min:60|max:100',
            'term_2' => 'nullable|numeric|min:60|max:100',
            'term_3' => 'nullable|numeric|min:60|max:100',
            'final_grade' => 'nullable|numeric|min:60|max:100',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $schoolYear = $this->getCurrentSchoolYear();
        $teacher = $this->authenticatedUser();

        // Find or create the grade record
        $grade = Grade::updateOrCreate(
            [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'school_year' => $schoolYear,
            ],
            [
                'section_id' => $request->section_id,
                'teacher_id' => $teacher->id,
                'term_1' => $request->term_1,
                'term_2' => $request->term_2,
                'term_3' => $request->term_3,
                'final_grade' => $request->final_grade,
            ]
        );

        return back()->with('success', 'Grades updated successfully');
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function availableSectionSchoolYears(string $currentSchoolYear)
    {
        $years = SchoolYear::catalog()->merge(
            Section::query()
                ->whereNotNull('school_year')
                ->pluck('school_year')
                ->map(fn ($year) => SchoolYear::normalize((string) $year))
        )->filter()->unique()->sortDesc()->values();

        if ($years->isEmpty()) {
            return collect([$currentSchoolYear])->filter()->values();
        }

        if ($currentSchoolYear && ! $years->contains($currentSchoolYear)) {
            $years->prepend($currentSchoolYear);
        }

        return $years->unique()->values();
    }

    private function resolveSectionSchoolYear(
        string $requested,
        string $currentSchoolYear,
        $availableSchoolYears,
    ): string {
        if ($requested !== '' && $availableSchoolYears->contains($requested)) {
            return $requested;
        }

        $yearWithMostSections = Section::query()
            ->selectRaw('school_year, COUNT(*) as total')
            ->whereNotNull('school_year')
            ->groupBy('school_year')
            ->orderByDesc('total')
            ->orderByDesc('school_year')
            ->value('school_year');

        if ($yearWithMostSections && $availableSchoolYears->contains($yearWithMostSections)) {
            return $yearWithMostSections;
        }

        return $currentSchoolYear;
    }

    /**
     * Prefer the current school-year enrollment, otherwise the student's latest record.
     *
     * @param  \Illuminate\Support\Collection<int, User>  $students
     */
    private function attachCurrentEnrollments($students, string $schoolYear): void
    {
        $enrollments = Enrollment::with(['yearLevel', 'section'])
            ->whereIn('user_id', $students->pluck('id'))
            ->orderByDesc('school_year')
            ->orderByDesc('id')
            ->get()
            ->groupBy('user_id');

        $students->each(function (User $student) use ($enrollments, $schoolYear) {
            $records = $enrollments->get($student->id, collect());
            $current = $records->first(
                fn (Enrollment $enrollment) => $enrollment->school_year === $schoolYear
            ) ?? $records->first();

            $student->setRelation('currentEnrollment', $current);
        });
    }

    /**
     * Get the active school year from Settings.
     */
    private function getCurrentSchoolYear(): string
    {
        return SchoolSetting::currentSchoolYear();
    }

    private function assertTeacherHandlesSubject(User $teacher, Subject $subject): void
    {
        $assigned = SectionSubjectTeacher::query()
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $subject->id)
            ->exists();

        $teachable = $teacher->teachableSubjects()
            ->where('subjects.id', $subject->id)
            ->exists();

        abort_unless($assigned || $teachable, 403);
    }

    private function assignmentMatchesSchoolYear(SectionSubjectTeacher $assignment, string $schoolYear): bool
    {
        $year = $assignment->school_year ?: $assignment->section?->school_year;

        return $year === $schoolYear;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Subject>  $teacherSubjects
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function teacherGradeRows(User $teacher, $teacherSubjects, string $schoolYear)
    {
        $allAssignments = SectionSubjectTeacher::query()
            ->where('teacher_id', $teacher->id)
            ->with(['section.yearLevel', 'subject'])
            ->get();

        $assignments = $allAssignments
            ->filter(fn (SectionSubjectTeacher $assignment) => $this->assignmentMatchesSchoolYear($assignment, $schoolYear))
            ->values();

        $existingGrades = Grade::query()
            ->where('school_year', $schoolYear)
            ->get()
            ->keyBy(fn (Grade $grade) => $grade->student_id.'-'.$grade->subject_id);

        $rows = collect();
        $seen = [];

        $pushRow = function ($student, $section, $subject, Enrollment $enrollment) use (&$rows, &$seen, $existingGrades) {
            $key = $student->id.'-'.$subject->id.'-'.($section?->id ?? 'none');
            if (isset($seen[$key])) {
                return;
            }
            $seen[$key] = true;

            $existingGrade = $existingGrades->get($student->id.'-'.$subject->id);
            $rows->push([
                'id' => $enrollment->id.'-'.$subject->id,
                'student' => $student,
                'section' => $section,
                'subject' => $subject,
                'term_1' => $existingGrade?->term_1,
                'term_2' => $existingGrade?->term_2,
                'term_3' => $existingGrade?->term_3,
                'final_grade' => $existingGrade?->final_grade,
                'subject_id' => $subject->id,
                'section_id' => $section?->id,
            ]);
        };

        foreach ($assignments as $assignment) {
            $section = $assignment->section;
            $subject = $assignment->subject;
            if (! $section || ! $subject) {
                continue;
            }

            $enrollments = Enrollment::query()
                ->where('section_id', $section->id)
                ->where('school_year', $schoolYear)
                ->where('status', 'enrolled')
                ->with('user')
                ->get();

            foreach ($enrollments as $enrollment) {
                if ($enrollment->user) {
                    $pushRow($enrollment->user, $section, $subject, $enrollment);
                }
            }
        }

        // Only fall back to a raw year-level roster when this teacher has
        // NEVER been assigned to any section (i.e. bootstrap / brand-new
        // teacher). If they have assignments in other school years, that is
        // a data issue — surface an empty list for the current SY so the
        // admin sees they need to add current-SY assignments, rather than
        // silently dumping every enrolled student in the year level.
        if ($rows->isEmpty() && $allAssignments->isEmpty()) {
            foreach ($teacherSubjects as $subject) {
                if (! $subject->year_level_id) {
                    continue;
                }

                $enrollments = Enrollment::query()
                    ->where('year_level_id', $subject->year_level_id)
                    ->where('school_year', $schoolYear)
                    ->where('status', 'enrolled')
                    ->with(['user', 'section.yearLevel'])
                    ->get();

                foreach ($enrollments as $enrollment) {
                    if ($enrollment->user) {
                        $pushRow($enrollment->user, $enrollment->section, $subject, $enrollment);
                    }
                }
            }
        }

        return $rows;
    }

    private function studentsForTeacherSubject(User $teacher, Subject $subject, string $schoolYear)
    {
        $assignments = SectionSubjectTeacher::query()
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $subject->id)
            ->with(['section.enrollments' => function ($query) use ($schoolYear) {
                $query->where('school_year', $schoolYear)
                    ->whereIn('status', ['enrolled', 'approved'])
                    ->with('user');
            }, 'section'])
            ->get()
            ->filter(fn (SectionSubjectTeacher $assignment) => $this->assignmentMatchesSchoolYear($assignment, $schoolYear))
            ->values();

        $students = collect();

        if ($assignments->isNotEmpty()) {
            foreach ($assignments as $assignment) {
                $section = $assignment->section;
                if (! $section) {
                    continue;
                }

                foreach ($section->enrollments ?? [] as $enrollment) {
                    if (! $enrollment->user) {
                        continue;
                    }

                    $student = $enrollment->user;
                    $student->setAttribute('section_name', $section->name);
                    $students->put($student->id, $student);
                }
            }
        } elseif ($subject->year_level_id) {
            $enrollments = Enrollment::query()
                ->where('year_level_id', $subject->year_level_id)
                ->where('school_year', $schoolYear)
                ->whereIn('status', ['enrolled', 'approved'])
                ->with(['user', 'section'])
                ->get();

            foreach ($enrollments as $enrollment) {
                if (! $enrollment->user) {
                    continue;
                }

                $student = $enrollment->user;
                $student->setAttribute('section_name', $enrollment->section?->name ?: 'N/A');
                $students->put($student->id, $student);
            }
        }

        return $students
            ->sortBy(fn (User $student) => strtoupper(trim($student->last_name.' '.$student->first_name)))
            ->values();
    }

    private function authenticatedUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
