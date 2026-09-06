<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPermanentRecord extends Model
{
    protected $fillable = [
        'student_id',
        'school_year',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'notes',
        'uploaded_by',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
