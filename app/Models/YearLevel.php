<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YearLevel extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'level_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'rank',
    ];

    /**
     * Get the sections for the year level.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    /**
     * Get the subjects for the year level.
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Get the enrollments for the year level.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Scope a query to only include active year levels.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order year levels by grade sequence.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('id');
    }

    public function getRankAttribute(): int
    {
        if (preg_match('/(\d+)/', (string) $this->code, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/(\d+)/', (string) $this->name, $matches)) {
            return (int) $matches[1];
        }

        return (int) $this->id;
    }
}
