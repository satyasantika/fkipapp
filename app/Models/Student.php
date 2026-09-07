<?php

namespace App\Models;

use App\Models\Concerns\GuardsDeletionWhenReferenced;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory, GuardsDeletionWhenReferenced;
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_proposal' => 'date',
        'tanggal_seminar' => 'date',
        'tanggal_skripsi' => 'date',
    ];

    public function examregistrations(): HasMany
    {
        return $this->hasMany(ExamRegistration::class);
    }

    /**
     * Registrasi ujian TERBARU per jenis ujian (dipakai halaman "Status Ujian
     * Mahasiswa" untuk memantau keterlambatan pelaporan/sinkronisasi).
     *
     * PENTING: CanBeOneOfMany::newOneOfManySubQuery() membangun subquery
     * agregat MAX() lewat $this->query->getModel()->newQuery() - query BARU
     * dari model, BUKAN clone $this->query - jadi where('exam_type_id', ...)
     * yang di-chain SEBELUM latestOfMany() TIDAK PERNAH ikut ke subquery
     * agregatnya sama sekali (cuma kepasang di query TERLUAR lewat
     * addConstraints()). Akibatnya kalau cuma pakai ->where()->latestOfMany()
     * biasa, MAX(tanggal_ujian) dihitung dari SEMUA jenis ujian mahasiswa itu
     * (bukan cuma jenis yang diminta) - baris terluar lalu gagal mencocokkan
     * (join ke tanggal MAX yang salah) dan hasilnya null, diam-diam, setiap
     * kali mahasiswa punya jenis ujian LAIN dengan tanggal lebih baru
     * (kebuktian lewat test - retake sempro + semhas bertanggal lebih baru
     * bikin latestSempro null). Perbaikannya: pakai ofMany() langsung dengan
     * closure constraint - closure itu DIJALANKAN di setiap subquery agregat
     * yang dibangun ($closure($subQuery) di dalam loop ofMany()), jadi
     * exam_type_id ikut membatasi MAX-nya juga, bukan cuma filter terluar.
     */
    public function latestSempro(): HasOne
    {
        return $this->hasOne(ExamRegistration::class)
            ->where('exam_type_id', 1)
            ->ofMany(['tanggal_ujian' => 'max'], function (Builder $query) {
                $query->where('exam_type_id', 1);
            });
    }

    public function latestSemhas(): HasOne
    {
        return $this->hasOne(ExamRegistration::class)
            ->where('exam_type_id', 2)
            ->ofMany(['tanggal_ujian' => 'max'], function (Builder $query) {
                $query->where('exam_type_id', 2);
            });
    }

    public function latestSidang(): HasOne
    {
        return $this->hasOne(ExamRegistration::class)
            ->where('exam_type_id', 3)
            ->ofMany(['tanggal_ujian' => 'max'], function (Builder $query) {
                $query->where('exam_type_id', 3);
            });
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    public function pembimbing1(): BelongsTo
    {
        return $this->belongsTo(Lecture::class, 'pembimbing1_id');
    }

    public function pembimbing2(): BelongsTo
    {
        return $this->belongsTo(Lecture::class, 'pembimbing2_id');
    }

    public function penguji1(): BelongsTo
    {
        return $this->belongsTo(Lecture::class, 'penguji1_id');
    }

    public function penguji2(): BelongsTo
    {
        return $this->belongsTo(Lecture::class, 'penguji2_id');
    }

    public function penguji3(): BelongsTo
    {
        return $this->belongsTo(Lecture::class, 'penguji3_id');
    }

    public function deletionBlockReason(): ?string
    {
        $count = $this->examregistrations()->count();

        if (! $count) {
            return null;
        }

        return "Mahasiswa ini masih memiliki {$count} registrasi ujian. Hapus registrasi ujian tersebut terlebih dahulu.";
    }
}
