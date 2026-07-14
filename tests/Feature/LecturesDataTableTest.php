<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsExamFixtures;
use Tests\TestCase;

class LecturesDataTableTest extends TestCase
{
    use RefreshDatabase;
    use SeedsExamFixtures;

    public function test_lecture_list_reads_from_lectures_table_directly(): void
    {
        $departement = $this->makeDepartement();
        $this->makeLecture($departement, ['nama' => 'Budi Santoso', 'pns' => true]);
        $admin = $this->makeUserWithRole('admin');

        $response = $this->actingAs($admin)->getJson(
            '/lectures?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 1);
        $response->assertJsonPath('data.0.nama', 'Budi Santoso');
        $response->assertJsonPath('data.0.pns', 'ASN');
    }

    public function test_jurusan_role_only_sees_own_departement(): void
    {
        $ownDepartement = $this->makeDepartement();
        $otherDepartement = $this->makeDepartement();
        $this->makeLecture($ownDepartement, ['nama' => 'Dosen Sendiri']);
        $this->makeLecture($otherDepartement, ['nama' => 'Dosen Jurusan Lain']);

        $jurusan = $this->makeUserWithRole('jurusan', $ownDepartement->id);

        $response = $this->actingAs($jurusan)->getJson(
            '/lectures?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 1);
        $response->assertJsonPath('data.0.nama', 'Dosen Sendiri');
    }
}
