<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_proposal' => 'date',
        'tanggal_seminar' => 'date',
        'tanggal_sidang' => 'date',
    ];

    public function examregistrations(): HasMany
    {
        return $this->hasMany(ExamRegistration::class);
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }
}
