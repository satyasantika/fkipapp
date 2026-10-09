<?php

namespace Tests\Feature;

use App\Models\ExamRegistration;
use App\Models\Lecture;
use App\Services\SintesysSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsExamFixtures;
use Tests\TestCase;

class SintesysSyncServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsExamFixtures;

    private function sintesysRow(string $tanggal, array $penguji = []): array
    {
        return [
            'nim' => '123456789',
            'nama' => 'Mahasiswa Test',
            'jenis_ujian' => 'Ujian Proposal',
            'tanggal_ujian' => $tanggal.' 08:00:00',
            'tempat_ujian' => 'R101',
            'judul_unformated' => 'Judul',
            'penguji' => $penguji,
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

    public function test_unreported_duplicate_on_reported_date_is_deleted_on_commit(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $examType = $this->makeExamType('sempro');
        $dilaporkan = $this->makeExamRegistration($departement, $student, $examType, ['tanggal_ujian' => '2026-07-14', 'dilaporkan' => 1]);
        $dobel = $this->makeExamRegistration($departement, $student, $examType, ['tanggal_ujian' => '2026-07-14', 'ujian_ke' => 2]);
        $tanggalLain = $this->makeExamRegistration($departement, $student, $examType, ['tanggal_ujian' => '2026-08-01', 'ujian_ke' => 3]);

        $service = new SintesysSyncService;
        $result = $service->analyze([$this->sintesysRow('2026-07-14')], $departement->id);

        $this->assertSame(1, $result['summary']['dobel_dihapus']);

        $summary = $service->commit($result['items']);

        $this->assertSame(1, $summary['dobel_dihapus']);
        $this->assertDatabaseMissing('exam_registrations', ['id' => $dobel->id]);
        $this->assertDatabaseHas('exam_registrations', ['id' => $dilaporkan->id, 'dilaporkan' => 1]);
        $this->assertDatabaseHas('exam_registrations', ['id' => $tanggalLain->id]);
    }

    public function test_lecturers_are_matched_by_nuptk_only(): void
    {
        $departement = $this->makeDepartement();
        $examType = $this->makeExamType('sempro');
        $lama = $this->makeLecture($departement, ['nidn' => '0011', 'nuptk' => '9001']);

        $service = new SintesysSyncService;
        $result = $service->analyze([$this->sintesysRow('2026-07-14', [
            ['urutan' => 1, 'nidn' => '0011', 'nuptk' => '9001', 'nama' => 'Dosen Lama'],
            ['urutan' => 2, 'nidn' => '0022', 'nuptk' => '9002', 'nama' => 'Dosen Baru'],
        ])], $departement->id);

        $slots = $result['items'][0]['lecture_slots'];
        $this->assertSame($lama->id, $slots['ketuapenguji_id']['existing_id']);
        $this->assertNull($slots['penguji1_id']['existing_id']);
        $this->assertSame(['Dosen Baru'], $result['items'][0]['dosen_baru']);

        $service->commit($result['items']);

        $baru = Lecture::where('nuptk', '9002')->firstOrFail();
        $this->assertSame('0022', $baru->nidn);
        $this->assertSame($lama->id, ExamRegistration::where('exam_type_id', $examType->id)->value('ketuapenguji_id'));
        $this->assertSame($baru->id, ExamRegistration::where('exam_type_id', $examType->id)->value('penguji1_id'));
    }

    public function test_commit_cleans_duplicates_outside_synced_dates(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $lain = $this->makeStudent($departement, ['nim' => '987654321']);
        $examType = $this->makeExamType('sempro');
        $semhas = $this->makeExamType('semhas', ['kode_ujian' => 'SH']);
        // Dobel lama (Maret) di luar tanggal yang disinkronkan.
        $this->makeExamRegistration($departement, $lain, $semhas, ['tanggal_ujian' => '2026-03-02', 'dilaporkan' => 1]);
        $dobelLapor = $this->makeExamRegistration($departement, $lain, $semhas, ['tanggal_ujian' => '2026-03-02']);
        $belumPertama = $this->makeExamRegistration($departement, $lain, $examType, ['tanggal_ujian' => '2026-02-02']);
        $belumKedua = $this->makeExamRegistration($departement, $lain, $examType, ['tanggal_ujian' => '2026-02-02']);

        $service = new SintesysSyncService;
        $result = $service->analyze([$this->sintesysRow('2026-07-14')], $departement->id);

        $this->assertSame(2, $result['summary']['dobel_dihapus']);

        $summary = $service->commit($result['items']);

        $this->assertSame(2, $summary['dobel_dihapus']);
        $this->assertDatabaseMissing('exam_registrations', ['id' => $dobelLapor->id]);
        $this->assertDatabaseMissing('exam_registrations', ['id' => $belumKedua->id]);
        $this->assertDatabaseHas('exam_registrations', ['id' => $belumPertama->id]);
        $this->assertSame(1, ExamRegistration::where('dilaporkan', true)->count());
        $this->assertSame(1, ExamRegistration::where('student_id', $student->id)->count());
    }

    public function test_existing_lecturer_without_nuptk_is_matched_by_nidn_and_filled(): void
    {
        $departement = $this->makeDepartement();
        $this->makeExamType('sempro');
        $lama = $this->makeLecture($departement, ['nidn' => '0011']);
        // NUPTK sudah terisi (berbeda) -> tidak ditimpa, jadi dosen baru.
        $terisi = $this->makeLecture($departement, ['nidn' => '0022', 'nuptk' => 'LAMA']);

        $service = new SintesysSyncService;
        $result = $service->analyze([$this->sintesysRow('2026-07-14', [
            ['urutan' => 1, 'nidn' => '0011', 'nuptk' => '9001', 'nama' => 'Dosen Lama'],
            ['urutan' => 2, 'nidn' => '0022', 'nuptk' => '9002', 'nama' => 'Dosen Lain'],
        ])], $departement->id);

        $this->assertSame($lama->id, $result['items'][0]['lecture_slots']['ketuapenguji_id']['existing_id']);
        $this->assertSame(1, $result['summary']['nuptk_diisi']);
        $this->assertSame(1, $result['summary']['dosen_baru']);

        $summary = $service->commit($result['items']);

        $this->assertSame(1, $summary['nuptk_diisi']);
        $this->assertSame('9001', $lama->fresh()->nuptk);
        $this->assertSame('LAMA', $terisi->fresh()->nuptk);
        $this->assertSame(1, Lecture::where('nidn', '0011')->count());
    }
}
