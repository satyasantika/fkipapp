<?php

namespace App\Models;

use App\Models\Concerns\GuardsDeletionWhenReferenced;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportDate extends Model
{
    use HasFactory, GuardsDeletionWhenReferenced;
    protected $guarded = ['id'];
    protected $casts = [
        'is_locked' => 'bool',
        'locked_at' => 'datetime',
        'last_pulled_at' => 'datetime',
    ];

    public function examiners(): HasMany
    {
        return $this->hasMany(ExamRegistration::class);
    }

    public function exam_payment_reports(): HasMany
    {
        return $this->hasMany(ExamPaymentReport::class);
    }

    public function deletionBlockReason(): ?string
    {
        $examCount = $this->examiners()->count();
        $reportCount = $this->exam_payment_reports()->count();

        $parts = [];
        if ($examCount) {
            $parts[] = "{$examCount} registrasi ujian";
        }
        if ($reportCount) {
            $parts[] = "{$reportCount} laporan honor";
        }

        if (! $parts) {
            return null;
        }

        return 'Tanggal penarikan laporan ini masih dipakai oleh '.implode(' dan ', $parts).'.';
    }
}
