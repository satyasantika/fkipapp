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
    ];

    public function examiners(): HasMany
    {
        return $this->hasMany(ExamExaminer::class);
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    public function exam_type(): BelongsTo
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
