<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamExaminer extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'ketua' => 'boolean',
        'aktif' => 'boolean',
    ];

    public function exam_registration(): BelongsTo
    {
        return $this->belongsTo(ExamRegistration::class, 'exam_registration_id');
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class, 'lecture_id');
    }

    public function exam_payment(): BelongsTo
    {
        return $this->belongsTo(ExamPayment::class, 'exam_payment_id');
    }
}
