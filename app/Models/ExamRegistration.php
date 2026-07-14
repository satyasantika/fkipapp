<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    public function reportdate(): BelongsTo
    {
        return $this->belongsTo(ReportDate::class, 'report_date_id');
    }

    // Query per periode laporan (mis. "2026-07"), dulu berupa kolom `kode_laporan`
    // di view_exam_registrations (LEFT(tanggal_ujian,7)). Dipakai untuk filter,
    // bukan cuma tampilan, jadi disediakan sebagai scope, bukan accessor saja.
    // Nama scope sengaja "whereKodeLaporan" (bukan "kodeLaporan") supaya tidak
    // collide dengan accessor kodeLaporan() di bawah — panggilan static
    // ExamRegistration::kodeLaporan(...) akan memanggil method accessor itu
    // langsung (bukan lewat mekanisme scope Eloquent), sama seperti kasus
    // pembimbing_1 di model Student.
    public function scopeWhereKodeLaporan($query, string $value)
    {
        return $query->whereRaw("DATE_FORMAT(tanggal_ujian, '%Y-%m') = ?", [$value]);
    }

    protected function kodeLaporan(): Attribute
    {
        return Attribute::make(get: fn () => $this->tanggal_ujian?->format('Y-m'));
    }

    protected function ujian(): Attribute
    {
        return Attribute::make(get: fn () => $this->exam_type?->singkat_ujian);
    }

    protected function mahasiswa(): Attribute
    {
        return Attribute::make(get: fn () => $this->student?->nama);
    }

    protected function nim(): Attribute
    {
        return Attribute::make(get: fn () => $this->student?->nim);
    }

    protected function pembimbing1Nama(): Attribute
    {
        return Attribute::make(get: fn () => $this->pembimbing1?->nama);
    }

    protected function pembimbing2Nama(): Attribute
    {
        return Attribute::make(get: fn () => $this->pembimbing2?->nama);
    }

    protected function penguji1Nama(): Attribute
    {
        return Attribute::make(get: fn () => $this->penguji1?->nama);
    }

    protected function penguji2Nama(): Attribute
    {
        return Attribute::make(get: fn () => $this->penguji2?->nama);
    }

    protected function penguji3Nama(): Attribute
    {
        return Attribute::make(get: fn () => $this->penguji3?->nama);
    }
}
