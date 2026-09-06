<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'profile_photo',
        'suffix',
        'email',
        'phone_no',
        'date_of_birth',
        'gender',
        'province',
        'municipality',
        'barangay',
        'lrn',
        'previous_gwa',
        'guardian_full_name',
        'guardian_contact_no',
        'guardian_relationship',
        'father_name',
        'father_occupation',
        'father_contact',
        'mother_name',
        'mother_occupation',
        'mother_contact',
        'blood_type',
        'allergies',
        'medical_conditions',
        'emergency_contact_person',
        'emergency_contact_number',
        'year_level_applying',
        'preferred_strand',
        'previous_school',
        'school_year_applying',
        'role',
        'admission_status',
        'admission_remarks',
        'admission_reviewed_by',
        'admission_reviewed_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'admission_reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->role !== 'student') {
                $user->admission_status = null;
                $user->admission_remarks = null;
                $user->admission_reviewed_by = null;
                $user->admission_reviewed_at = null;
            } elseif (blank($user->admission_status)) {
                $user->admission_status = 'pending';
            }
        });
    }

    public function isAdmissionApproved(): bool
    {
        return $this->role === 'student' && $this->admission_status === 'approved';
    }

    public function admissionReviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admission_reviewed_by');
    }

    /**
     * Get the subjects this teacher can teach.
     */
    public function teachableSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects', 'teacher_id', 'subject_id')
            ->withTimestamps();
    }

    /**
     * Get the section-subject assignments for this teacher.
     */
    public function sectionSubjectAssignments(): HasMany
    {
        return $this->hasMany(SectionSubjectTeacher::class, 'teacher_id');
    }

    /**
     * Get sections where this teacher is an adviser.
     */
    public function advisedSections(): HasMany
    {
        return $this->hasMany(Section::class, 'adviser_id');
    }

    /**
     * Get enrollments for this student.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function permanentRecords(): HasMany
    {
        return $this->hasMany(StudentPermanentRecord::class, 'student_id');
    }

    public function previousYearLevel(?string $currentSchoolYear = null): ?YearLevel
    {
        $query = $this->enrollments()
            ->whereIn('status', ['enrolled', 'approved'])
            ->with('yearLevel')
            ->orderByDesc('school_year')
            ->orderByDesc('id');

        if ($currentSchoolYear) {
            $query->where('school_year', '!=', $currentSchoolYear);
        }

        $enrollment = $query->first();
        if ($enrollment?->yearLevel) {
            return $enrollment->yearLevel;
        }

        if ($this->year_level_applying) {
            return YearLevel::query()
                ->where('name', $this->year_level_applying)
                ->orWhere('code', $this->year_level_applying)
                ->first();
        }

        return null;
    }
}
