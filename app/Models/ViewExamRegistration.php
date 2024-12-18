<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewExamRegistration extends Model
{
    use HasFactory;
    protected $table = 'view_exam_registrations';

    public function reportdate(): BelongsTo
    {
        return $this->belongsTo(ReportDate::class, 'report_date_id');
    }

}
