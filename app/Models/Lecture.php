<?php

namespace App\Models;

use App\Models\Concerns\GuardsDeletionWhenReferenced;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lecture extends Model
{
    use HasFactory, GuardsDeletionWhenReferenced;
    protected $guarded = ['id'];
    protected $casts = [
        'tanggal_lahir' => 'date',
        'tmt_jabatan_akademik' => 'date',
        'tmt_golongan' => 'date',
        'tmt_pendidikan' => 'date',
        'pns' => 'bool',
    ];

    public function examiners(): HasMany
    {
        return $this->hasMany(ExamExaminer::class);
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    public function pembimbing1Registrations(): HasMany
    {
        return $this->hasMany(ExamRegistration::class, 'pembimbing1_id');
    }

    public function pembimbing2Registrations(): HasMany
    {
        return $this->hasMany(ExamRegistration::class, 'pembimbing2_id');
    }

    public function penguji1Registrations(): HasMany
    {
        return $this->hasMany(ExamRegistration::class, 'penguji1_id');
    }

    public function penguji2Registrations(): HasMany
    {
        return $this->hasMany(ExamRegistration::class, 'penguji2_id');
    }

    public function penguji3Registrations(): HasMany
    {
        return $this->hasMany(ExamRegistration::class, 'penguji3_id');
    }

    private const LECTURE_ROLE_COLUMNS = [
        'pembimbing1_id', 'pembimbing2_id',
        'penguji1_id', 'penguji2_id', 'penguji3_id',
        'ketuapenguji_id',
    ];

    public function deletionBlockReason(): ?string
    {
        $matchesAnyRoleColumn = function ($query) {
            foreach (self::LECTURE_ROLE_COLUMNS as $column) {
                $query->orWhere($column, $this->id);
            }
        };

        $asStudentPembimbingPenguji = Student::where($matchesAnyRoleColumn)->count();
        $asExamRegistrationPembimbingPenguji = ExamRegistration::where($matchesAnyRoleColumn)->count();
        $inPaymentReports = ExamPaymentReport::where('lecture_id', $this->id)->count();

        $parts = [];
        if ($asStudentPembimbingPenguji) {
            $parts[] = "{$asStudentPembimbingPenguji} data mahasiswa (sebagai pembimbing/penguji)";
        }
        if ($asExamRegistrationPembimbingPenguji) {
            $parts[] = "{$asExamRegistrationPembimbingPenguji} registrasi ujian";
        }
        if ($inPaymentReports) {
            $parts[] = "{$inPaymentReports} laporan honor";
        }

        if (! $parts) {
            return null;
        }

        return 'Dosen ini masih dipakai di '.implode(', ', $parts).'. Ubah atau hapus data tersebut terlebih dahulu.';
    }
}
