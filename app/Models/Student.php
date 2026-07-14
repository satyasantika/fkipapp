<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;
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

}
