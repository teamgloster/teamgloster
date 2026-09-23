<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Strand extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'label',
    ];

    public function getLabelAttribute(): string
    {
        if ($this->name === '' || $this->name === $this->code) {
            return (string) $this->code;
        }

        return $this->code.' — '.$this->name;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('code')->orderBy('name');
    }

    public static function formOptions()
    {
        return static::query()
            ->active()
            ->ordered()
            ->get(['id', 'name', 'code']);
    }

    public static function labelFor(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $strand = static::query()
            ->where(function (Builder $query) use ($value) {
                $query->where('code', $value)
                    ->orWhere('name', $value);
            })
            ->first();

        return $strand?->label ?? $value;
    }
}
