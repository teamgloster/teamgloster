<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRequirement extends Model
{
    protected $table = 'student_requirements';

    protected $fillable = [
        'user_id',
        'requirement_type',
        'file_path',
        'original_filename',
        'status',
        'remarks',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
