<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsExamFixtures;
use Tests\TestCase;

class StudentsDataTableTest extends TestCase
{
    use RefreshDatabase;
    use SeedsExamFixtures;

    public function test_pembimbing_and_penguji_names_are_resolved_from_lecture_relations(): void
    {
        $departement = $this->makeDepartement();
        $pembimbing1 = $this->makeLecture($departement, ['nama' => 'Pembimbing Satu']);
        $penguji1 = $this->makeLecture($departement, ['nama' => 'Penguji Satu']);
        $this->makeStudent($departement, [
            'nama' => 'Galih Surya',
            'pembimbing1_id' => $pembimbing1->id,
            'penguji1_id' => $penguji1->id,
        ]);
        $admin = $this->makeUserWithRole('admin');

        $response = $this->actingAs($admin)->getJson(
            '/students?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 1);
        $response->assertJsonPath('data.0.nama', 'Galih Surya');
        $response->assertJsonPath('data.0.pembimbing_1', 'Pembimbing Satu');
        $response->assertJsonPath('data.0.penguji_1', 'Penguji Satu');
    }

    public function test_student_without_advisor_shows_blank_not_error(): void
    {
        $departement = $this->makeDepartement();
        $this->makeStudent($departement, ['nama' => 'Belum Ada Pembimbing']);
        $admin = $this->makeUserWithRole('admin');

        $response = $this->actingAs($admin)->getJson(
            '/students?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $response->assertJsonPath('data.0.pembimbing_1', '');
    }

    public function test_search_by_pembimbing_name_filters_via_relation(): void
    {
        $departement = $this->makeDepartement();
        $pembimbing1 = $this->makeLecture($departement, ['nama' => 'Nama Unik Sekali']);
        $this->makeStudent($departement, ['nama' => 'Mahasiswa A', 'pembimbing1_id' => $pembimbing1->id]);
        $this->makeStudent($departement, ['nama' => 'Mahasiswa B']);
        $admin = $this->makeUserWithRole('admin');

        $columns = ['departement_id', 'nim', 'nama', 'pembimbing_1', 'pembimbing_2', 'penguji_1', 'penguji_2', 'penguji_3', 'tanggal_proposal', 'tanggal_seminar', 'tanggal_skripsi'];
        $params = ['draw' => 1, 'start' => 0, 'length' => 10, 'search' => ['value' => 'Unik Sekali', 'regex' => 'false']];
        foreach ($columns as $i => $name) {
            $params['columns'][$i] = ['data' => $name, 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']];
        }

        $response = $this->actingAs($admin)->getJson(
            '/students?'.http_build_query($params),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $response->assertJsonPath('recordsFiltered', 1);
        $response->assertJsonPath('data.0.nama', 'Mahasiswa A');
    }
}
