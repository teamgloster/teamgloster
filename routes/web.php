<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentSummaryController;
use App\Http\Controllers\PermanentRecordController;
use App\Http\Controllers\RegistrarController;
use App\Http\Controllers\SchoolFormController;
use App\Http\Controllers\StudentEnrollmentController;
use App\Http\Controllers\TranscriptionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing page
Route::get('/', function () {
    return Inertia::render('landing');
})->name('home');

// Authentication routes
Route::get('/login/{role}', [AuthController::class, 'showLogin'])
    ->where('role', 'administrator|registrar|teacher|student')
    ->name('login');

Route::get('/register/student', [AuthController::class, 'showRegister'])
    ->name('register');

Route::get('/admission/apply', [AuthController::class, 'showRegister'])
    ->name('admission.apply');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Protected dashboard routes
Route::middleware(['auth'])->group(function () {
    // Legacy admin route - redirect to new dashboard
    Route::get('/dashboard/administrator', function () {
        return redirect()->route('admin.dashboard');
    })->middleware('role:administrator');

    // New Admin Routes
    Route::middleware('role:administrator')->prefix('admin')->group(function () {
        Route::get('/', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
        Route::get('/students', [DashboardController::class, 'adminStudents'])->name('admin.students');
        Route::get('/teachers', [DashboardController::class, 'adminTeachers'])->name('admin.teachers');
        Route::get('/enrollments', [DashboardController::class, 'adminEnrollments'])->name('admin.enrollments');
        Route::get('/admissions', [DashboardController::class, 'adminAdmissions'])->name('admin.admissions');
        Route::get('/sections', [DashboardController::class, 'adminSections'])->name('admin.sections');
        Route::get('/subjects', [DashboardController::class, 'adminSubjects'])->name('admin.subjects');
        Route::get('/year-levels', [DashboardController::class, 'adminYearLevels'])->name('admin.year-levels');
        Route::get('/teacher-assignments', [DashboardController::class, 'adminTeacherAssignments'])->name('admin.teacher-assignments');
        Route::get('/requirements', [DashboardController::class, 'adminRequirements'])->name('admin.requirements');
        Route::get('/accounts', [DashboardController::class, 'adminAccounts'])->name('admin.accounts');
        Route::get('/settings', [DashboardController::class, 'adminSettings'])->name('admin.settings');
        Route::get('/school-forms', [SchoolFormController::class, 'adminIndex'])->name('admin.school-forms');
        Route::get('/school-forms/sf1/{section}', [SchoolFormController::class, 'adminSf1'])->name('admin.school-forms.sf1');
        Route::get('/school-forms/sf2/{section}', [SchoolFormController::class, 'adminSf2'])->name('admin.school-forms.sf2');
        Route::get('/students/{student}/sf9', [SchoolFormController::class, 'adminSf9'])->name('admin.students.sf9');
        Route::get('/students/{student}/sf10', [SchoolFormController::class, 'adminSf10'])->name('admin.students.sf10');
        Route::get('/permanent-records', fn () => redirect()->route('admin.school-forms'))->name('admin.permanent-records');
        Route::post('/students/{student}/permanent-records', [PermanentRecordController::class, 'store'])->name('admin.permanent-records.store');
        Route::get('/permanent-records/{record}/download', [PermanentRecordController::class, 'download'])->name('admin.permanent-records.download');
        Route::delete('/permanent-records/{record}', [PermanentRecordController::class, 'destroy'])->name('admin.permanent-records.destroy');
        Route::get('/enrollment-summary', [EnrollmentSummaryController::class, 'adminIndex'])->name('admin.enrollment-summary');
    });

    Route::get('/dashboard/registrar', function () {
        return redirect()->route('registrar.dashboard');
    })->middleware('role:registrar');

    Route::middleware('role:registrar')->prefix('registrar')->group(function () {
        Route::get('/', [RegistrarController::class, 'dashboard'])->name('registrar.dashboard');
        Route::get('/year-levels', [RegistrarController::class, 'yearLevels'])->name('registrar.year-levels');
        Route::get('/sections', [RegistrarController::class, 'sections'])->name('registrar.sections');
        Route::get('/sections/{section}/enrollment', [RegistrarController::class, 'sectionEnrollment'])->name('registrar.sections.enrollment');
        Route::get('/grades', [RegistrarController::class, 'grades'])->name('registrar.grades');
        Route::get('/students', [RegistrarController::class, 'students'])->name('registrar.students');
        Route::get('/students/{student}/enrollment', [RegistrarController::class, 'studentEnrollment'])->name('registrar.students.enrollment');
        Route::post('/students/promote-selected', [RegistrarController::class, 'promoteSelected']);
        Route::post('/students/{student}/promote', [RegistrarController::class, 'promoteStudent']);
        Route::put('/students/{student}/year-level', [RegistrarController::class, 'changeStudentYearLevel']);
        Route::get('/students/{student}/sf9', [SchoolFormController::class, 'registrarSf9'])->name('registrar.students.sf9');
        Route::get('/students/{student}/sf10', [SchoolFormController::class, 'registrarSf10'])->name('registrar.students.sf10');
        Route::get('/permanent-records', [PermanentRecordController::class, 'registrarIndex'])->name('registrar.permanent-records');
        Route::post('/students/{student}/permanent-records', [PermanentRecordController::class, 'store'])->name('registrar.permanent-records.store');
        Route::get('/permanent-records/{record}/download', [PermanentRecordController::class, 'download'])->name('registrar.permanent-records.download');
        Route::delete('/permanent-records/{record}', [PermanentRecordController::class, 'destroy'])->name('registrar.permanent-records.destroy');
        Route::get('/enrollment-summary', [EnrollmentSummaryController::class, 'registrarIndex'])->name('registrar.enrollment-summary');
    });

    Route::get('/dashboard/teacher', [DashboardController::class, 'teacher'])
        ->middleware('role:teacher');

    Route::middleware('role:teacher')->prefix('teacher')->group(function () {
        Route::get('/subjects/{subject}/students', [DashboardController::class, 'teacherSubjectStudents'])->name('teacher.subjects.students');
        Route::get('/school-forms/sf1/{section}', [SchoolFormController::class, 'teacherSf1'])->name('teacher.school-forms.sf1');
        Route::get('/school-forms/sf2/{section}', [SchoolFormController::class, 'teacherSf2'])->name('teacher.school-forms.sf2');
        Route::get('/students/{student}/sf9', [SchoolFormController::class, 'teacherSf9'])->name('teacher.students.sf9');
        Route::get('/students/{student}/sf10', [SchoolFormController::class, 'teacherSf10'])->name('teacher.students.sf10');
    });

    // Legacy student route - redirect to new dashboard
    Route::get('/dashboard/student', function () {
        return redirect()->route('student.dashboard');
    })->middleware('role:student');

    // New Student Routes
    Route::middleware('role:student')->prefix('student')->group(function () {
        Route::get('/', [DashboardController::class, 'studentDashboard'])->name('student.dashboard');
        Route::get('/profile', [DashboardController::class, 'studentProfile'])->name('student.profile');
        Route::get('/enrollment', [DashboardController::class, 'studentEnrollment'])->name('student.enrollment');
        Route::get('/subjects', [DashboardController::class, 'studentSubjects'])->name('student.subjects');
        Route::get('/grades', [DashboardController::class, 'studentGrades'])->name('student.grades');
        Route::get('/sf9', [SchoolFormController::class, 'studentSf9'])->name('student.sf9');
        Route::get('/sf10', [SchoolFormController::class, 'studentSf10'])->name('student.sf10');
        Route::get('/requirements', [DashboardController::class, 'studentRequirements'])->name('student.requirements');
    });

    // Teacher grade update route
    Route::put('/teacher/grades/{studentId}/{subjectId}', [DashboardController::class, 'updateGrade'])
        ->middleware('role:teacher');

    // Teacher voice transcription (Groq Whisper)
    Route::post('/teacher/transcribe', [TranscriptionController::class, 'transcribe'])
        ->middleware('role:teacher')
        ->name('teacher.transcribe');

    // Profile update routes
    Route::put('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/profile/photo', [AuthController::class, 'updateProfilePhoto']);
    Route::delete('/profile/photo', [AuthController::class, 'removeProfilePhoto']);

    // Student requirements routes
    Route::post('/requirements/upload', [AuthController::class, 'uploadRequirement']);
    Route::post('/requirements/remove', [AuthController::class, 'removeRequirement']);

    // Student enrollment routes
    Route::prefix('enrollment')->group(function () {
        Route::get('/sections', [StudentEnrollmentController::class, 'getSections']);
        Route::get('/subjects', [StudentEnrollmentController::class, 'getSubjects']);
        Route::post('/submit', [StudentEnrollmentController::class, 'submitEnrollment']);
        Route::post('/cancel', [StudentEnrollmentController::class, 'cancelEnrollment']);
    });

    // Admin routes
    Route::middleware('role:administrator')->prefix('admin')->group(function () {
        // Student management
        Route::post('/students', [AdminController::class, 'storeStudent']);
        Route::post('/students/promote-selected', [AdminController::class, 'promoteSelected']);
        Route::put('/students/{student}', [AdminController::class, 'updateStudent']);
        Route::delete('/students/{student}', [AdminController::class, 'deleteStudent']);
        Route::post('/students/{student}/promote', [AdminController::class, 'promoteStudent']);
        Route::put('/students/{student}/year-level', [AdminController::class, 'changeStudentYearLevel']);

        // Teacher management
        Route::post('/teachers', [AdminController::class, 'storeTeacher']);
        Route::put('/teachers/{teacher}', [AdminController::class, 'updateTeacher']);
        Route::delete('/teachers/{teacher}', [AdminController::class, 'deleteTeacher']);

        // Staff accounts (administrator and registrar)
        Route::post('/accounts', [AdminController::class, 'storeAccount'])->name('admin.accounts.store');
        Route::put('/accounts/{account}', [AdminController::class, 'updateAccount'])->name('admin.accounts.update');
        Route::delete('/accounts/{account}', [AdminController::class, 'deleteAccount'])->name('admin.accounts.destroy');

        // Admission management
        Route::post('/admissions/{student}/approve', [AdminController::class, 'approveAdmission']);
        Route::post('/admissions/{student}/reject', [AdminController::class, 'rejectAdmission']);

        // Enrollment management
        Route::post('/enrollments/{enrollment}/approve', [AdminController::class, 'approveEnrollment']);
        Route::post('/enrollments/approve-selected', [AdminController::class, 'approveSelected']);
        Route::post('/enrollments/{enrollment}/reject', [AdminController::class, 'rejectEnrollment']);
        Route::post('/enrollments/{enrollment}/enroll', [AdminController::class, 'enrollStudent']);
        Route::post('/enrollments/enroll-selected', [AdminController::class, 'enrollSelected']);
        Route::post('/enrollments/{enrollment}/assign-section', [AdminController::class, 'assignSection']);
        Route::post('/enrollments/{enrollment}/transfer-section', [AdminController::class, 'transferSection']);
        Route::post('/enrollments/auto-assign-sections', [AdminController::class, 'autoAssignSections']);
        Route::post('/enrollments/reshuffle-by-grades', [AdminController::class, 'reshuffleSectionsByGrades']);
        Route::post('/enrollments/{enrollment}/drop', [AdminController::class, 'dropEnrollment']);

        // Year level management
        Route::post('/year-levels', [AdminController::class, 'storeYearLevel']);
        Route::put('/year-levels/{yearLevel}', [AdminController::class, 'updateYearLevel']);
        Route::delete('/year-levels/{yearLevel}', [AdminController::class, 'deleteYearLevel']);

        // Section management
        Route::post('/sections', [AdminController::class, 'storeSection']);
        Route::put('/sections/{section}', [AdminController::class, 'updateSection']);
        Route::delete('/sections/{section}', [AdminController::class, 'deleteSection']);

        // Subject management
        Route::post('/subjects', [AdminController::class, 'storeSubject']);
        Route::put('/subjects/{subject}', [AdminController::class, 'updateSubject']);
        Route::delete('/subjects/{subject}', [AdminController::class, 'deleteSubject']);

        // Teacher Subject Assignments (what subjects a teacher can teach)
        Route::post('/teacher-subjects', [AdminController::class, 'storeTeacherSubject']);
        Route::delete('/teacher-subjects/{teacherSubject}', [AdminController::class, 'deleteTeacherSubject']);
        Route::post('/teacher-subjects/bulk', [AdminController::class, 'bulkAssignTeacherSubjects']);

        // Section Subject Teacher Assignments (which teacher teaches what subject in which section)
        Route::post('/section-subject-teachers', [AdminController::class, 'storeSectionSubjectTeacher']);
        Route::put('/section-subject-teachers/{assignment}', [AdminController::class, 'updateSectionSubjectTeacher']);
        Route::delete('/section-subject-teachers/{assignment}', [AdminController::class, 'deleteSectionSubjectTeacher']);
        Route::get('/subjects/{subject}/teachers', [AdminController::class, 'getTeachersForSubject']);

        // Student Requirements management
        Route::put('/requirements/{requirement}/verify', [AdminController::class, 'verifyRequirement']);
        Route::put('/requirements/{requirement}/reject', [AdminController::class, 'rejectRequirement']);

        // Password reset
        Route::post('/users/{user}/reset-password', [AdminController::class, 'resetPassword']);

        // School settings
        Route::put('/settings', [AdminController::class, 'updateSettings']);
        Route::put('/settings/password', [AdminController::class, 'updateAdminPassword']);
        Route::post('/settings/school-years', [AdminController::class, 'storeAcademicYear'])->name('admin.school-years.store');
        Route::delete('/settings/school-years/{academicYear}', [AdminController::class, 'destroyAcademicYear'])->name('admin.school-years.destroy');
    });
});
