<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamRegistration extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_ujian' => 'date',
        'penguji1_dibayar' => 'bool',
        'penguji2_dibayar' => 'bool',
        'penguji3_dibayar' => 'bool',
        'pembimbing1_dibayar' => 'bool',
        'pembimbing2_dibayar' => 'bool',
        'dilaporkan' => 'bool',
    ];

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    public function exam_type()
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
    }

    public function penguji1()
    {
        return $this->belongsTo(Lecture::class, 'penguji1_id');
    }

    public function penguji2()
    {
        return $this->belongsTo(Lecture::class, 'penguji2_id');
    }

    public function penguji3()
    {
        return $this->belongsTo(Lecture::class, 'penguji3_id');
    }

    public function pembimbing1()
    {
        return $this->belongsTo(Lecture::class, 'pembimbing1_id');
    }

    public function pembimbing2()
    {
        return $this->belongsTo(Lecture::class, 'pembimbing2_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
