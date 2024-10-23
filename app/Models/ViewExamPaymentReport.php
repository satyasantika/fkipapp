<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewExamPaymentReport extends Model
{
    use HasFactory;
    protected $table = 'view_exam_payment_reports';

    public function reportdate(): BelongsTo
    {
        return $this->belongsTo(ReportDate::class, 'report_date_id');
    }
}
