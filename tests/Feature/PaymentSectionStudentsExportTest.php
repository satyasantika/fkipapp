<?php

namespace Tests\Feature;

use App\Models\ReportDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Feature\Concerns\SeedsExamFixtures;
use Tests\TestCase;

class PaymentSectionStudentsExportTest extends TestCase
{
    use RefreshDatabase;
    use SeedsExamFixtures;

    public function test_keuangan_can_download_student_exam_list_for_a_report_date(): void
    {
        $departement = $this->makeDepartement();
        $examType = $this->makeExamType('sempro');
        $penguji1 = $this->makeLecture($departement, ['nama' => 'Penguji Satu']);
        $penguji2 = $this->makeLecture($departement, ['nama' => 'Penguji Dua']);
        $penguji3 = $this->makeLecture($departement, ['nama' => 'Penguji Tiga']);
        $pembimbing1 = $this->makeLecture($departement, ['nama' => 'Pembimbing Satu']);
        $pembimbing2 = $this->makeLecture($departement, ['nama' => 'Pembimbing Dua']);

        $reportDate = ReportDate::create(['tanggal' => '2026-07-14']);
        $otherReportDate = ReportDate::create(['tanggal' => '2026-08-14']);

        $student = $this->makeStudent($departement, [
            'nama' => 'Galih Surya',
            'nim' => '172103001',
        ]);
        $this->makeExamRegistration($departement, $student, $examType, [
            'report_date_id' => $reportDate->id,
            'tanggal_ujian' => '2026-07-10',
            'penguji1_id' => $penguji1->id,
            'penguji2_id' => $penguji2->id,
            'penguji3_id' => $penguji3->id,
            'pembimbing1_id' => $pembimbing1->id,
            'pembimbing2_id' => $pembimbing2->id,
        ]);

        $otherStudent = $this->makeStudent($departement, [
            'nama' => 'Mahasiswa Lain',
            'nim' => '172103999',
        ]);
        $this->makeExamRegistration($departement, $otherStudent, $examType, [
            'report_date_id' => $otherReportDate->id,
            'tanggal_ujian' => '2026-08-10',
            'penguji1_id' => $penguji1->id,
        ]);

        $keuangan = $this->makeUserWithRole('keuangan');

        $response = $this->actingAs($keuangan)->get(route('reportdates.students.export', [
            'report_date_id' => $reportDate->id,
        ]));

        $response->assertOk();
        $response->assertDownload('mahasiswa-ujian-2026-07-14.xlsx');

        $sheet = IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet();

        $this->assertSame([
            'npm',
            'nama',
            'ujian',
            'tanggal',
            'penguji 1',
            'penguji 2',
            'penguji 3',
            'pembimbing 1',
            'pembimbing 2',
        ], $sheet->rangeToArray('A1:I1')[0]);

        $this->assertSame([
            '172103001',
            'Galih Surya',
            'sempro',
            '2026-07-10',
            'Penguji Satu',
            'Penguji Dua',
            'Penguji Tiga',
            'Pembimbing Satu',
            'Pembimbing Dua',
        ], $sheet->rangeToArray('A2:I2')[0]);

        $this->assertNull($sheet->getCell('A3')->getValue());
        $this->assertSame('s', $sheet->getCell('A2')->getDataType());
    }
}
