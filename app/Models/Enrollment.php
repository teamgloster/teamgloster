<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    protected $attributes = [
        'status' => 'pending',
    ];

    protected $fillable = [
        'user_id',
        'year_level_id',
        'section_id',
        'school_year',
        'semester',
        'status',
        'enrollment_type',
        'previous_gwa',
        'previous_school',
        'remarks',
        'enrolled_at',
        'approved_by',
    ];

    protected $casts = [
        'previous_gwa' => 'decimal:2',
        'enrolled_at' => 'datetime',
    ];

    /**
     * Get the student for this enrollment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the year level for this enrollment.
     */
    public function yearLevel(): BelongsTo
    {
        return $this->belongsTo(YearLevel::class);
    }

    /**
     * Get the section for this enrollment.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the admin who approved this enrollment.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for pending enrollments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for enrolled status.
     */
    public function scopeEnrolled($query)
    {
        return $query->where('status', 'enrolled');
    }

    /**
     * Scope for current school year.
     */
    public function scopeCurrentYear($query, string $schoolYear)
    {
        return $query->where('school_year', $schoolYear);
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'enrolled' => 'green',
            'rejected' => 'red',
            'dropped' => 'gray',
            default => 'gray',
        };
    }
}
