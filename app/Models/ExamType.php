<?php

namespace App\Models;

use App\Models\Concerns\GuardsDeletionWhenReferenced;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamType extends Model
{
    use HasFactory, GuardsDeletionWhenReferenced;
    protected $guarded = ['id'];

    public function examregistrations(): HasMany
    {
        return $this->hasMany(ExamRegistration::class);
    }

    public function deletionBlockReason(): ?string
    {
        $count = $this->examregistrations()->count();

        if (! $count) {
            return null;
        }

        return "Jenis ujian ini masih dipakai oleh {$count} registrasi ujian.";
    }
}
