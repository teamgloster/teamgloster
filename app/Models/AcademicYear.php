<?php

namespace App\Models;

use App\Support\SchoolYear;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'year',
    ];

    public function setYearAttribute(?string $value): void
    {
        $this->attributes['year'] = SchoolYear::normalize($value) ?? $value;
    }

    public function isCurrent(): bool
    {
        return $this->year === SchoolSetting::currentSchoolYear();
    }

    public function isInUse(): bool
    {
        return Section::query()->where('school_year', $this->year)->exists()
            || Enrollment::query()->where('school_year', $this->year)->exists()
            || Grade::query()->where('school_year', $this->year)->exists()
            || SectionSubjectTeacher::query()->where('school_year', $this->year)->exists();
    }
}
