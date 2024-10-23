<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportDate extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function examiners(): HasMany
    {
        return $this->hasMany(ExamRegistration::class);
    }

    public function exam_payment_reports(): HasMany
    {
        return $this->hasMany(ExamPaymentReport::class);
    }
}
