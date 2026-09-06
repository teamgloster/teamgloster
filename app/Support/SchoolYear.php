<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\SchoolSetting;
use Illuminate\Support\Collection;

class SchoolYear
{
    public const PATTERN = '/^\d{4}-\d{4}$/';

    /**
     * Collapse typed variants ("2025 - 2026", "SY 2025/2026") into one official
     * value: consecutive calendar years as YYYY-YYYY.
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match_all('/\d{4}/', $trimmed, $matches) < 1) {
            return null;
        }

        $years = array_map('intval', $matches[0]);
        $start = $years[0];
        $end = $years[1] ?? ($start + 1);

        if ($end !== $start + 1) {
            return null;
        }

        if ($start < 2000 || $start > 2100) {
            return null;
        }

        return $start.'-'.$end;
    }

    public static function isValid(?string $value): bool
    {
        return self::normalize($value) !== null;
    }

    /**
     * Unique official school years already used in the system.
     *
     * @return Collection<int, string>
     */
    public static function catalog(): Collection
    {
        $years = AcademicYear::query()->pluck('year');

        $current = self::normalize(SchoolSetting::currentSchoolYear());
        if ($current) {
            $years->push($current);
        }

        return $years
            ->map(fn ($year) => self::normalize((string) $year))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();
    }

    /**
     * School years the admin has set up. No generated presets.
     *
     * @return Collection<int, string>
     */
    public static function selectable(): Collection
    {
        return self::catalog();
    }
}
