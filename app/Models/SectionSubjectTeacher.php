<?php

namespace App\Models;

use App\Support\SchoolYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionSubjectTeacher extends Model
{
    protected $fillable = [
        'section_id',
        'subject_id',
        'teacher_id',
        'school_year',
        'semester',
        'schedule',
        'room',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the section.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the subject.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function setSchoolYearAttribute(?string $value): void
    {
        $this->attributes['school_year'] = SchoolYear::normalize($value) ?? $value;
    }
}
