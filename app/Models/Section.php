<?php

namespace App\Models;

use App\Support\SchoolYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = [
        'year_level_id',
        'name',
        'code',
        'adviser_id',
        'capacity',
        'school_year',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    /**
     * Get the year level that the section belongs to.
     */
    public function yearLevel(): BelongsTo
    {
        return $this->belongsTo(YearLevel::class);
    }

    /**
     * Get the adviser (teacher) for the section.
     */
    public function adviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }

    /**
     * Get the students in this section.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'section_id')->where('role', 'student');
    }

    /**
     * Get the enrollments for this section.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the subject-teacher assignments for this section.
     */
    public function subjectTeachers(): HasMany
    {
        return $this->hasMany(SectionSubjectTeacher::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)->withTimestamps();
    }

    /**
     * Scope a query to only include active sections.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the full section name (e.g., "Grade 7 - Section A")
     */
    public function getFullNameAttribute(): string
    {
        return $this->yearLevel->name . ' - ' . $this->name;
    }

    public function setSchoolYearAttribute(?string $value): void
    {
        $this->attributes['school_year'] = SchoolYear::normalize($value) ?? $value;
    }
}
