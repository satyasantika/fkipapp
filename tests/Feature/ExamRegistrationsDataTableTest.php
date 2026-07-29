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
        $this->assertStringContainsString('sempro', $response->json('data.0.ujian'));
        $this->assertStringContainsString('bg-secondary', $response->json('data.0.ujian'));
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

    public function test_tanggal_ujian_column_shows_date_only(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $examType = $this->makeExamType('sempro');
        $this->makeExamRegistration($departement, $student, $examType, ['tanggal_ujian' => '2026-07-14']);
        $admin = $this->makeUserWithRole('admin');

        $response = $this->actingAs($admin)->getJson(
            '/exam/registrations?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $response->assertJsonPath('data.0.tanggal_ujian', '2026-07-14');
    }

    public function test_dilaporkan_column_shows_representative_icon(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $examType = $this->makeExamType('sempro');
        $this->makeExamRegistration($departement, $student, $examType, ['dilaporkan' => 1]);
        $this->makeExamRegistration($departement, $student, $examType, ['dilaporkan' => 0]);
        $admin = $this->makeUserWithRole('admin');

        $response = $this->actingAs($admin)->getJson(
            '/exam/registrations?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();

        $rows = collect($response->json('data'));
        $reported = $rows->first(fn ($row) => str_contains($row['dilaporkan'], 'bi-check-circle-fill'));
        $notReported = $rows->first(fn ($row) => str_contains($row['dilaporkan'], 'bi-x-circle-fill'));

        $this->assertNotNull($reported);
        $this->assertStringContainsString('text-success', $reported['dilaporkan']);
        $this->assertNotNull($notReported);
        $this->assertStringContainsString('text-danger', $notReported['dilaporkan']);
    }

    public function test_ujian_column_shows_distinct_badge_per_exam_type(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $sempro = $this->makeExamType('sempro');
        $semhas = $this->makeExamType('semhas');
        $sidang = $this->makeExamType('sidang');
        $this->makeExamRegistration($departement, $student, $sempro);
        $this->makeExamRegistration($departement, $student, $semhas);
        $this->makeExamRegistration($departement, $student, $sidang);
        $admin = $this->makeUserWithRole('admin');

        $response = $this->actingAs($admin)->getJson(
            '/exam/registrations?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();

        $rows = collect($response->json('data'));
        $semproBadge = $rows->first(fn ($row) => str_contains($row['ujian'], '>sempro<'));
        $semhasBadge = $rows->first(fn ($row) => str_contains($row['ujian'], '>semhas<'));
        $sidangBadge = $rows->first(fn ($row) => str_contains($row['ujian'], '>sidang<'));

        $this->assertNotNull($semproBadge);
        $this->assertStringContainsString('bg-secondary', $semproBadge['ujian']);
        $this->assertNotNull($semhasBadge);
        $this->assertStringContainsString('bg-info', $semhasBadge['ujian']);
        $this->assertNotNull($sidangBadge);
        $this->assertStringContainsString('bg-primary', $sidangBadge['ujian']);
    }
}
