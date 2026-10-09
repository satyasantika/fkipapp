<?php

namespace Tests\Feature;

use App\Models\ExamRegistration;
use App\Services\SintesysSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsExamFixtures;
use Tests\TestCase;

class SintesysSyncServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsExamFixtures;

    private function sintesysRow(string $tanggal): array
    {
        return [
            'nim' => '123456789',
            'nama' => 'Mahasiswa Test',
            'jenis_ujian' => 'Ujian Proposal',
            'tanggal_ujian' => $tanggal.' 08:00:00',
            'tempat_ujian' => 'R101',
            'judul_unformated' => 'Judul',
            'penguji' => [],
        ];
    }

    public function test_reported_exam_on_same_date_is_skipped(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $examType = $this->makeExamType('sempro');
        $this->makeExamRegistration($departement, $student, $examType, [
            'tanggal_ujian' => '2026-07-14',
            'dilaporkan' => 1,
            'ujian_ke' => 1,
        ]);

        $service = new SintesysSyncService;
        $result = $service->analyze([$this->sintesysRow('2026-07-14')], $departement->id);

        $this->assertSame('dilewati', $result['items'][0]['status']);
        $this->assertTrue($result['items'][0]['sudah_dilaporkan']);
        $this->assertSame(1, $result['summary']['dilewati_sudah_dilaporkan']);

        $summary = $service->commit($result['items']);

        $this->assertSame(1, $summary['dilewati_sudah_dilaporkan']);
        $this->assertSame(0, $summary['dilewati_jenis_tidak_dikenal']);
        $this->assertSame(1, ExamRegistration::count());
    }

    public function test_reported_exam_on_other_date_creates_retake(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $examType = $this->makeExamType('sempro');
        $this->makeExamRegistration($departement, $student, $examType, [
            'tanggal_ujian' => '2026-06-01',
            'dilaporkan' => 1,
            'ujian_ke' => 1,
        ]);

        $result = (new SintesysSyncService)->analyze([$this->sintesysRow('2026-07-14')], $departement->id);

        $this->assertSame('dibuat', $result['items'][0]['status']);
        $this->assertSame(2, $result['items'][0]['ujian_ke_baru']);
    }

    public function test_unreported_exam_is_updated(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $examType = $this->makeExamType('sempro');
        $registration = $this->makeExamRegistration($departement, $student, $examType, [
            'tanggal_ujian' => '2026-07-10',
            'ujian_ke' => 1,
        ]);

        $result = (new SintesysSyncService)->analyze([$this->sintesysRow('2026-07-14')], $departement->id);

        $this->assertSame('diperbarui', $result['items'][0]['status']);
        $this->assertSame($registration->id, $result['items'][0]['existing_registration_id']);
    }
}
