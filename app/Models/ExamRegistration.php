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

    public function ketuapenguji()
    {
        return $this->belongsTo(Lecture::class, 'ketuapenguji_id');
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

    // Kebijakan honor hanya membayar SATU kali per jenis ujian (sempro/semhas/
    // sidang) per mahasiswa. "Ujian ulang" = ujian BELUM dilaporkan yang
    // tidak dihitung lagi karena ada ujian jenis sama (mahasiswa sama) yang
    // lebih baru, ATAU yang sudah dilaporkan (honornya sudah dibayar). Ujian
    // ulang disembunyikan dari daftar/hitungan "belum dilaporkan" dan tidak
    // bisa dilaporkan - hanya tampil di menu "Ujian Ulang". Ujian yang sudah
    // dilaporkan TIDAK PERNAH dianggap ujian ulang (riwayat honor tetap utuh).
    public function scopeUjianUlang($query)
    {
        $table = $query->getQuery()->from;

        return $query->where("{$table}.dilaporkan", false)
            ->whereExists(self::ujianPenggantiQuery($table));
    }

    public function scopeTanpaUjianUlang($query)
    {
        $table = $query->getQuery()->from;

        return $query->where(fn ($q) => $q->where("{$table}.dilaporkan", true)
            ->orWhereNotExists(self::ujianPenggantiQuery($table)));
    }

    public function isUjianUlang(): bool
    {
        return static::whereKey($this->getKey())->ujianUlang()->exists();
    }

    /**
     * Ujian jenis sama yang membuat baris ini jadi ujian ulang: yang sudah
     * dilaporkan diutamakan, kalau tidak ada -> yang terbaru.
     */
    public function ujianPengganti(): ?self
    {
        return static::where('student_id', $this->student_id)
            ->where('exam_type_id', $this->exam_type_id)
            ->whereKeyNot($this->getKey())
            ->orderByDesc('dilaporkan')
            ->orderByDesc('tanggal_ujian')
            ->orderByDesc('id')
            ->first();
    }

    // Ujian lain (mahasiswa & jenis sama) yang "menggantikan" baris $table:
    // sudah dilaporkan, atau lebih baru (tanggal lebih besar; tanggal sama ->
    // id lebih besar).
    private static function ujianPenggantiQuery(string $table): \Closure
    {
        return fn ($sub) => $sub->selectRaw('1')
            ->from('exam_registrations as ujian_lain')
            ->whereColumn('ujian_lain.student_id', "{$table}.student_id")
            ->whereColumn('ujian_lain.exam_type_id', "{$table}.exam_type_id")
            ->whereColumn('ujian_lain.id', '<>', "{$table}.id")
            ->where(fn ($q) => $q->where('ujian_lain.dilaporkan', true)
                ->orWhereColumn('ujian_lain.tanggal_ujian', '>', "{$table}.tanggal_ujian")
                ->orWhere(fn ($q) => $q->whereColumn('ujian_lain.tanggal_ujian', "{$table}.tanggal_ujian")
                    ->whereColumn('ujian_lain.id', '>', "{$table}.id")));
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

    /**
     * Daftar penguji berurutan: ketua penguji dulu, lalu penguji1/2/3.
     * Ada prodi yang menjadikan pembimbing sebagai ketua - dalam kasus itu
     * ketua dilewati supaya pembimbing tidak ikut terhitung sebagai penguji.
     *
     * @return \Illuminate\Support\Collection<int, Lecture>
     */
    public function pengujiBerurutan(): \Illuminate\Support\Collection
    {
        $ketuaAdalahPembimbing = $this->ketuapenguji_id
            && in_array($this->ketuapenguji_id, [$this->pembimbing1_id, $this->pembimbing2_id]);

        return collect([
            $ketuaAdalahPembimbing ? null : $this->ketuapenguji,
            $this->penguji1,
            $this->penguji2,
            $this->penguji3,
        ])->filter()->unique('id')->values();
    }
}
