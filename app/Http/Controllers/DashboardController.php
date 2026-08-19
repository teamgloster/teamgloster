<?php

namespace App\Http\Controllers;

use App\Models\StudentRequirement;
use App\Models\Enrollment;
use App\Models\YearLevel;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Models\TeacherSubject;
use App\Models\SectionSubjectTeacher;
use App\Models\Grade;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
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
            'user' => auth()->user(),
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
            'user' => auth()->user(),
            'stats' => $stats,
            'recentEnrollments' => $recentEnrollments,
            'enrollmentByYearLevel' => $enrollmentByYearLevel,
            'currentSchoolYear' => $currentSchoolYear,
        ]);
    }

    /**
     * Admin Students Management Page
     */
    public function adminStudents()
    {
        $currentSchoolYear = $this->getCurrentSchoolYear();

        $students = User::where('role', 'student')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $enrollments = Enrollment::with(['yearLevel', 'section'])
            ->where('school_year', $currentSchoolYear)
            ->whereIn('user_id', $students->pluck('id'))
            ->orderByDesc('id')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $students->each(function (User $student) use ($enrollments) {
            $student->setRelation('currentEnrollment', $enrollments->get($student->id));
        });

        $sections = Section::with('yearLevel')
            ->where('school_year', $currentSchoolYear)
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get();

        $yearLevels = YearLevel::ordered()->get();

        return Inertia::render('Dashboard/Admin/Students', [
            'user' => auth()->user(),
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
            'user' => auth()->user(),
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
        
        $sections = Section::with(['yearLevel'])
            ->where('school_year', $currentSchoolYear)
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get();
        
        $yearLevels = YearLevel::ordered()->get();
        
        return Inertia::render('Dashboard/Admin/Enrollments', [
            'user' => auth()->user(),
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
            'user' => auth()->user(),
            'applicants' => $applicants,
        ]);
    }

    /**
     * Admin Sections Management Page
     */
    public function adminSections()
    {
        $currentSchoolYear = $this->getCurrentSchoolYear();
        
        $sections = Section::with(['yearLevel', 'adviser'])
            ->withCount(['enrollments' => function ($query) {
                $query->where('status', 'enrolled');
            }])
            ->where('school_year', $currentSchoolYear)
            ->orderBy('year_level_id')
            ->orderBy('name')
            ->get();
        
        $yearLevels = YearLevel::ordered()->get();
        
        $teachers = User::where('role', 'teacher')
            ->orderBy('last_name')
            ->get();
        
        return Inertia::render('Dashboard/Admin/Sections', [
            'user' => auth()->user(),
            'sections' => $sections,
            'yearLevels' => $yearLevels,
            'teachers' => $teachers,
            'currentSchoolYear' => $currentSchoolYear,
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
            'user' => auth()->user(),
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
            'user' => auth()->user(),
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
            'user' => auth()->user(),
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
            'user' => auth()->user(),
            'studentRequirements' => $studentRequirements,
        ]);
    }

    /**
     * Admin Settings Page
     */
    public function adminSettings()
    {
        return Inertia::render('Dashboard/Admin/Settings', [
            'user' => auth()->user(),
            'settings' => SchoolSetting::current(),
        ]);
    }

    public function teacher()
    {
        $user = auth()->user();

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
                ->map(fn($enrollment) => $enrollment->user)
            : collect();

        // Get teacher's assigned subjects (what they CAN teach)
        $teacherSubjects = $user->teachableSubjects()->with('yearLevel')->get();

        // Get teacher's section-subject assignments (what they ARE teaching)
        $sectionSubjectAssignments = SectionSubjectTeacher::where('teacher_id', $user->id)
            ->with(['section.enrollments.user', 'subject', 'section'])
            ->get();

        // Get teacher's assigned sections (through SectionSubjectTeacher)
        $teacherSections = Section::whereHas('subjectTeachers', function ($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })->with(['enrollments.user', 'yearLevel'])->get();

        // Fallback: If no section assignments exist, get sections based on teacher's subjects' year levels
        if ($teacherSections->isEmpty() && $teacherSubjects->isNotEmpty()) {
            $yearLevelIds = $teacherSubjects->pluck('year_level_id')->filter()->unique();
            $teacherSections = Section::whereIn('year_level_id', $yearLevelIds)
                ->with(['enrollments.user', 'yearLevel'])
                ->get();
        }

        // Get student grades for this teacher's classes
        $studentGrades = collect();
        $currentSchoolYear = $this->getCurrentSchoolYear();
        
        // Get all existing grades for this school year
        $existingGrades = Grade::where('school_year', $currentSchoolYear)
            ->get()
            ->keyBy(function ($grade) {
                return $grade->student_id . '-' . $grade->subject_id;
            });
        
        // If teacher has section assignments, use those
        if ($sectionSubjectAssignments->count() > 0) {
            foreach ($sectionSubjectAssignments as $assignment) {
                $section = $assignment->section;
                $subject = $assignment->subject;
                
                if (!$section || !$subject) continue;
                
                $enrollments = $section->enrollments ?? collect();
                
                foreach ($enrollments as $enrollment) {
                    if ($enrollment->status !== 'enrolled' || !$enrollment->user) continue;
                    
                    $gradeKey = $enrollment->user->id . '-' . $subject->id;
                    $existingGrade = $existingGrades->get($gradeKey);
                    
                    $studentGrades->push([
                        'id' => $enrollment->id . '-' . $subject->id,
                        'student' => $enrollment->user,
                        'section' => $section,
                        'subject' => $subject,
                        'term_1' => $existingGrade?->term_1,
                        'term_2' => $existingGrade?->term_2,
                        'term_3' => $existingGrade?->term_3,
                        'final_grade' => $existingGrade?->final_grade,
                        'subject_id' => $subject->id,
                        'section_id' => $section->id,
                    ]);
                }
            }
        } else {
            // Fallback: Get students based on subject's year level
            foreach ($teacherSubjects as $subject) {
                if (!$subject->year_level_id) continue;
                
                // Get all enrollments for this year level
                $enrollments = Enrollment::where('year_level_id', $subject->year_level_id)
                    ->where('status', 'enrolled')
                    ->with(['user', 'section'])
                    ->get();
                
                foreach ($enrollments as $enrollment) {
                    if (!$enrollment->user) continue;
                    
                    $gradeKey = $enrollment->user->id . '-' . $subject->id;
                    $existingGrade = $existingGrades->get($gradeKey);
                    
                    $studentGrades->push([
                        'id' => $enrollment->id . '-' . $subject->id,
                        'student' => $enrollment->user,
                        'section' => $enrollment->section,
                        'subject' => $subject,
                        'term_1' => $existingGrade?->term_1,
                        'term_2' => $existingGrade?->term_2,
                        'term_3' => $existingGrade?->term_3,
                        'final_grade' => $existingGrade?->final_grade,
                        'subject_id' => $subject->id,
                        'section_id' => $enrollment->section_id,
                    ]);
                }
            }
        }

        return Inertia::render('Dashboard/Teacher', [
            'user' => $user,
            'advisorySection' => $advisorySection,
            'advisoryStudents' => $advisoryStudents,
            'teacherSubjects' => $teacherSubjects,
            'teacherSections' => $teacherSections,
            'studentGrades' => $studentGrades,
            'currentSchoolYear' => $currentSchoolYear,
            'currentTerm' => SchoolSetting::current()->current_term,
        ]);
    }

    public function student()
    {
        $user = auth()->user();
        
        // Get or create student requirements
        $requirementTypes = ['form_137', 'picture_2x2', 'birth_certificate', 'good_moral_certificate'];
        
        foreach ($requirementTypes as $type) {
            StudentRequirement::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'requirement_type' => $type,
                ]
            );
        }
        
        // Get all requirements for the user
        $requirements = StudentRequirement::where('user_id', $user->id)
            ->get()
            ->map(function ($req) {
                return [
                    'id' => match($req->requirement_type) {
                        'form_137' => 1,
                        'picture_2x2' => 2,
                        'birth_certificate' => 3,
                        'good_moral_certificate' => 4,
                    },
                    'type' => $req->requirement_type,
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
                                ? $assignment->teacher->first_name . ' ' . $assignment->teacher->last_name 
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
        $user = auth()->user();
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
                                ? $assignment->teacher->first_name . ' ' . $assignment->teacher->last_name 
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
            'user' => auth()->user(),
        ]);
    }

    /**
     * Student Enrollment Page
     */
    public function studentEnrollment()
    {
        $user = auth()->user();
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
        $user = auth()->user();
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
        $user = auth()->user();
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
        $user = auth()->user();
        
        // Get or create student requirements
        $requirementTypes = ['form_137', 'picture_2x2', 'birth_certificate', 'good_moral_certificate'];
        
        foreach ($requirementTypes as $type) {
            StudentRequirement::firstOrCreate([
                'user_id' => $user->id,
                'requirement_type' => $type,
            ]);
        }
        
        // Get all requirements for the user
        $requirements = StudentRequirement::where('user_id', $user->id)
            ->get()
            ->map(function ($req) {
                return [
                    'id' => match($req->requirement_type) {
                        'form_137' => 1,
                        'picture_2x2' => 2,
                        'birth_certificate' => 3,
                        'good_moral_certificate' => 4,
                        default => 0,
                    },
                    'name' => match($req->requirement_type) {
                        'form_137' => 'Form 137',
                        'picture_2x2' => '2x2 ID Picture',
                        'birth_certificate' => 'Birth Certificate (PSA)',
                        'good_moral_certificate' => 'Good Moral Certificate',
                        default => $req->requirement_type,
                    },
                    'description' => match($req->requirement_type) {
                        'form_137' => 'Academic records from previous school',
                        'picture_2x2' => 'Recent 2x2 ID photo with white background',
                        'birth_certificate' => 'Original PSA/NSO certified birth certificate',
                        'good_moral_certificate' => 'Certificate of Good Moral Character from previous school',
                        default => '',
                    },
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
        $teacher = auth()->user();

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
     * Get the active school year from Settings.
     */
    private function getCurrentSchoolYear(): string
    {
        return SchoolSetting::currentSchoolYear();
    }
}

