<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewExamDate extends Model
{
    use HasFactory;
    protected $table = 'view_exam_dates';

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }
}
