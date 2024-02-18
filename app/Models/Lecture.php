<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function examiners(): HasMany
    {
        return $this->hasMany(ExamExaminer::class);
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }
}
