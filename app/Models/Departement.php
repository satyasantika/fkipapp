<?php

namespace App\Models;

use App\Models\Concerns\GuardsDeletionWhenReferenced;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departement extends Model
{
    use HasFactory, GuardsDeletionWhenReferenced;
    protected $guarded = ['id'];

    public function examregistrations(): HasMany
    {
        return $this->hasMany(ExamRegistration::class);
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function deletionBlockReason(): ?string
    {
        $counts = [
            'dosen' => $this->lectures()->count(),
            'mahasiswa' => $this->students()->count(),
            'pengguna' => $this->users()->count(),
            'registrasi ujian' => $this->examregistrations()->count(),
        ];

        $used = array_filter($counts);

        if (! $used) {
            return null;
        }

        $parts = [];
        foreach ($used as $label => $count) {
            $parts[] = "{$count} {$label}";
        }

        return 'Jurusan ini masih dipakai oleh '.implode(', ', $parts).'. Pindahkan atau hapus data tersebut terlebih dahulu.';
    }
}
