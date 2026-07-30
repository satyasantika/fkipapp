<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsExamFixtures;
use Tests\TestCase;

class ExamRegistrationByDateDataTableTest extends TestCase
{
    use RefreshDatabase;
    use SeedsExamFixtures;

    public function test_dilaporkan_and_ujian_columns_show_icon_and_badge(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $sempro = $this->makeExamType('sempro');
        $semhas = $this->makeExamType('semhas');
        $this->makeExamRegistration($departement, $student, $sempro, [
            'tanggal_ujian' => '2026-07-14',
            'dilaporkan' => 1,
        ]);
        $this->makeExamRegistration($departement, $student, $semhas, [
            'tanggal_ujian' => '2026-07-14',
            'dilaporkan' => 0,
        ]);

        $jurusan = $this->makeUserWithRole('jurusan', $departement->id);

        $response = $this->actingAs($jurusan)->getJson(
            '/exam/reports/date/2026-07-14?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 2);

        $rows = collect($response->json('data'));

        $reported = $rows->first(fn ($row) => str_contains($row['dilaporkan'], 'bi-check-circle-fill'));
        $notReported = $rows->first(fn ($row) => str_contains($row['dilaporkan'], 'bi-x-circle-fill'));
        $this->assertNotNull($reported);
        $this->assertStringContainsString('text-success', $reported['dilaporkan']);
        $this->assertNotNull($notReported);
        $this->assertStringContainsString('text-danger', $notReported['dilaporkan']);

        $semproBadge = $rows->first(fn ($row) => str_contains($row['ujian'], '>sempro<'));
        $semhasBadge = $rows->first(fn ($row) => str_contains($row['ujian'], '>semhas<'));
        $this->assertNotNull($semproBadge);
        $this->assertStringContainsString('bg-secondary', $semproBadge['ujian']);
        $this->assertNotNull($semhasBadge);
        $this->assertStringContainsString('bg-info', $semhasBadge['ujian']);
    }

    public function test_default_sort_is_ruangan_asc_then_waktu_desc(): void
    {
        $departement = $this->makeDepartement();
        $jurusan = $this->makeUserWithRole('jurusan', $departement->id);

        $response = $this->actingAs($jurusan)->get('/exam/reports/date/2026-07-14');

        $response->assertOk();
        $response->assertSee('"order":[[2,"asc"],[3,"desc"]]', false);
    }
}
