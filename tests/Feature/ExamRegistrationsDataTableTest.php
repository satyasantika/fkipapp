<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsExamFixtures;
use Tests\TestCase;

class ExamRegistrationsDataTableTest extends TestCase
{
    use RefreshDatabase;
    use SeedsExamFixtures;

    public function test_derived_columns_are_resolved_from_relations_not_a_db_view(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement, ['nama' => 'Galih Surya', 'nim' => '172103001']);
        $examType = $this->makeExamType('sempro');
        $pembimbing1 = $this->makeLecture($departement, ['nama' => 'Lilis Karwati']);
        $this->makeExamRegistration($departement, $student, $examType, [
            'pembimbing1_id' => $pembimbing1->id,
        ]);
        $admin = $this->makeUserWithRole('admin');

        $response = $this->actingAs($admin)->getJson(
            '/exam/registrations?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 1);
        $response->assertJsonPath('data.0.ujian', 'sempro');
        $response->assertJsonPath('data.0.nim', '172103001');
        $response->assertJsonPath('data.0.mahasiswa', 'Galih Surya');
        $response->assertJsonPath('data.0.pembimbing1_nama', 'Lilis Karwati');
    }

    public function test_jurusan_role_is_scoped_to_own_departement(): void
    {
        $ownDepartement = $this->makeDepartement();
        $otherDepartement = $this->makeDepartement();
        $examType = $this->makeExamType();
        $this->makeExamRegistration($ownDepartement, $this->makeStudent($ownDepartement), $examType);
        $this->makeExamRegistration($otherDepartement, $this->makeStudent($otherDepartement), $examType);

        $jurusan = $this->makeUserWithRole('jurusan', $ownDepartement->id);

        $response = $this->actingAs($jurusan)->getJson(
            '/exam/registrations?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 1);
    }
}
