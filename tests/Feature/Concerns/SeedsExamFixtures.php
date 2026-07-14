<?php

namespace Tests\Feature\Concerns;

use App\Models\Departement;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\Lecture;
use App\Models\Student;
use App\Models\User;
use Spatie\Permission\Models\Role;

trait SeedsExamFixtures
{
    protected function makeUserWithRole(string $role, ?int $departementId = null): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create([
            'username' => 'test_'.$role.'_'.uniqid(),
            'departement_id' => $departementId,
        ]);
        $user->assignRole($role);

        return $user;
    }

    protected function makeDepartement(): Departement
    {
        return Departement::create(['nama' => 'Pendidikan Matematika', 'mapel' => 'Matematika']);
    }

    protected function makeLecture(Departement $departement, array $attributes = []): Lecture
    {
        return Lecture::create(array_merge([
            'departement_id' => $departement->id,
            'nama' => 'Dosen Test',
            'gelar_depan' => 'Dr.',
            'gelar_belakang' => 'M.Pd.',
        ], $attributes));
    }

    protected function makeStudent(Departement $departement, array $attributes = []): Student
    {
        return Student::create(array_merge([
            'departement_id' => $departement->id,
            'nama' => 'Mahasiswa Test',
            'nim' => '123456789',
        ], $attributes));
    }

    protected function makeExamType(string $singkat = 'sempro', array $attributes = []): ExamType
    {
        return ExamType::create(array_merge([
            'nama_ujian' => 'Ujian Seminar Proposal',
            'kode_ujian' => 'SP',
            'singkat_ujian' => $singkat,
        ], $attributes));
    }

    protected function makeExamRegistration(
        Departement $departement,
        Student $student,
        ExamType $examType,
        array $attributes = []
    ): ExamRegistration {
        return ExamRegistration::create(array_merge([
            'departement_id' => $departement->id,
            'student_id' => $student->id,
            'exam_type_id' => $examType->id,
            'tanggal_ujian' => '2026-07-14',
            'ruangan' => 'R101',
            'dilaporkan' => 0,
        ], $attributes));
    }
}
