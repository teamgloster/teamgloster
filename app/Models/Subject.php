<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'year_level_id',
        'subject_type',
        'semester',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the year level that the subject belongs to.
     */
    public function yearLevel(): BelongsTo
    {
        return $this->belongsTo(YearLevel::class);
    }

    /**
     * Scope a query to only include active subjects.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by subject type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('subject_type', $type);
    }

    /**
     * Scope a query to filter by semester.
     */
    public function scopeBySemester($query, string $semester)
    {
        return $query->where('semester', $semester);
    }
}
