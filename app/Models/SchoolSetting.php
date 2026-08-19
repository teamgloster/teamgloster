<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = [
        'school_name',
        'school_id',
        'region',
        'division',
        'district',
        'address',
        'municipality',
        'province',
        'contact_number',
        'email',
        'school_head',
        'current_school_year',
        'current_term',
        'enrollment_open',
    ];

    protected $casts = [
        'current_term' => 'integer',
        'enrollment_open' => 'boolean',
    ];

    public static function current(): self
    {
        $settings = static::query()->first();

        if ($settings) {
            return $settings;
        }

        return static::create(static::defaults());
    }

    public static function currentSchoolYear(): string
    {
        return static::current()->current_school_year;
    }

    public static function enrollmentOpen(): bool
    {
        return (bool) static::current()->enrollment_open;
    }

    public static function defaults(): array
    {
        $month = (int) date('n');
        $year = (int) date('Y');

        return [
            'school_name' => 'Tambo National High School',
            'school_id' => null,
            'region' => 'Region V (Bicol)',
            'division' => 'Schools Division of Camarines Sur',
            'district' => 'Buhi',
            'address' => 'Tambo, Buhi, Camarines Sur',
            'municipality' => 'Buhi',
            'province' => 'Camarines Sur',
            'contact_number' => null,
            'email' => 'admin@tnhs.edu.ph',
            'school_head' => null,
            'current_school_year' => $month >= 8
                ? $year.'-'.($year + 1)
                : ($year - 1).'-'.$year,
            'current_term' => 1,
            'enrollment_open' => true,
        ];
    }
}
