<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'section_id',
        'teacher_id',
        'school_year',
        'term_1',
        'term_2',
        'term_3',
        'final_grade',
    ];

    protected $casts = [
        'term_1' => 'decimal:2',
        'term_2' => 'decimal:2',
        'term_3' => 'decimal:2',
        'final_grade' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
