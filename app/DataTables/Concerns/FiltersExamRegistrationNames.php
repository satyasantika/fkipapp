<?php

namespace App\DataTables\Concerns;

use App\Models\ExamType;
use App\Models\Lecture;
use App\Models\Student;
use Yajra\DataTables\EloquentDataTable;

/**
 * Kolom ujian/nim/mahasiswa/pembimbingN_nama/pengujiN_nama dulu berupa kolom
 * hasil join di view_exam_registrations. Sekarang jadi accessor PHP di model
 * ExamRegistration, jadi supaya tetap bisa disortir/dicari lewat DataTable
 * (bukan cuma tampil), filter/order-nya perlu di-map balik ke SQL di sini.
 */
trait FiltersExamRegistrationNames
{
    protected function applyExamRegistrationNameColumns(EloquentDataTable $dataTable): EloquentDataTable
    {
        return $dataTable
            // ujian/nim/mahasiswa tidak diregister lewat editColumn di masing-masing
            // DataTable (beda dari pembimbingN_nama/pengujiN_nama) — tanpa ini,
            // Yajra menyerialisasi lewat toArray() yang tidak menyertakan accessor
            // yang tidak masuk $appends, sehingga selalu tampil null di JSON.
            ->editColumn('ujian', fn ($row) => $row->ujian ?? '')
            ->editColumn('nim', fn ($row) => $row->nim ?? '')
            ->editColumn('mahasiswa', fn ($row) => $row->mahasiswa ?? '')
            ->filterColumn('ujian', fn ($q, $kw) => $q->whereHas('exam_type', fn ($q2) => $q2->where('singkat_ujian', 'like', "%{$kw}%")))
            ->orderColumn('ujian', fn ($q, $dir) => $q->orderBy(ExamType::select('singkat_ujian')->whereColumn('exam_types.id', 'exam_registrations.exam_type_id'), $dir))
            ->filterColumn('nim', fn ($q, $kw) => $q->whereHas('student', fn ($q2) => $q2->where('nim', 'like', "%{$kw}%")))
            ->orderColumn('nim', fn ($q, $dir) => $q->orderBy(Student::select('nim')->whereColumn('students.id', 'exam_registrations.student_id'), $dir))
            ->filterColumn('mahasiswa', fn ($q, $kw) => $q->whereHas('student', fn ($q2) => $q2->where('nama', 'like', "%{$kw}%")))
            ->orderColumn('mahasiswa', fn ($q, $dir) => $q->orderBy(Student::select('nama')->whereColumn('students.id', 'exam_registrations.student_id'), $dir))
            ->filterColumn('pembimbing1_nama', fn ($q, $kw) => $q->whereHas('pembimbing1', fn ($q2) => $q2->where('nama', 'like', "%{$kw}%")))
            ->orderColumn('pembimbing1_nama', fn ($q, $dir) => $q->orderBy(Lecture::select('nama')->whereColumn('lectures.id', 'exam_registrations.pembimbing1_id'), $dir))
            ->filterColumn('pembimbing2_nama', fn ($q, $kw) => $q->whereHas('pembimbing2', fn ($q2) => $q2->where('nama', 'like', "%{$kw}%")))
            ->orderColumn('pembimbing2_nama', fn ($q, $dir) => $q->orderBy(Lecture::select('nama')->whereColumn('lectures.id', 'exam_registrations.pembimbing2_id'), $dir))
            ->filterColumn('penguji1_nama', fn ($q, $kw) => $q->whereHas('penguji1', fn ($q2) => $q2->where('nama', 'like', "%{$kw}%")))
            ->orderColumn('penguji1_nama', fn ($q, $dir) => $q->orderBy(Lecture::select('nama')->whereColumn('lectures.id', 'exam_registrations.penguji1_id'), $dir))
            ->filterColumn('penguji2_nama', fn ($q, $kw) => $q->whereHas('penguji2', fn ($q2) => $q2->where('nama', 'like', "%{$kw}%")))
            ->orderColumn('penguji2_nama', fn ($q, $dir) => $q->orderBy(Lecture::select('nama')->whereColumn('lectures.id', 'exam_registrations.penguji2_id'), $dir))
            ->filterColumn('penguji3_nama', fn ($q, $kw) => $q->whereHas('penguji3', fn ($q2) => $q2->where('nama', 'like', "%{$kw}%")))
            ->orderColumn('penguji3_nama', fn ($q, $dir) => $q->orderBy(Lecture::select('nama')->whereColumn('lectures.id', 'exam_registrations.penguji3_id'), $dir));
    }

    protected function eagerLoadExamRegistrationNames($query)
    {
        return $query->with(['exam_type', 'student', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2', 'penguji3']);
    }
}
