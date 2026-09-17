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
}
