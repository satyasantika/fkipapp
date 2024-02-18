<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departement extends Model
{
    use HasFactory;
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
}
