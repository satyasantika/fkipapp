<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportDateController;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\ReportDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\SeedsExamFixtures;
use Tests\TestCase;

class UjianUlangTest extends TestCase
{
    use RefreshDatabase;
    use SeedsExamFixtures;

    public function test_older_unreported_attempt_is_ujian_ulang(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $sempro = $this->makeExamType('sempro');
        $lama = $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-06-01']);
        $baru = $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-07-14']);

        $this->assertSame([$lama->id], ExamRegistration::ujianUlang()->pluck('id')->all());
        $this->assertSame([$baru->id], ExamRegistration::tanpaUjianUlang()->pluck('id')->all());
    }

    public function test_newer_attempt_after_reported_one_is_ujian_ulang(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $sempro = $this->makeExamType('sempro');
        $lama = $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-06-01', 'dilaporkan' => 1]);
        $baru = $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-07-14']);

        $this->assertSame([$baru->id], ExamRegistration::ujianUlang()->pluck('id')->all());
        $this->assertSame([$lama->id], ExamRegistration::tanpaUjianUlang()->pluck('id')->all());
        $this->assertSame($lama->id, $baru->ujianPengganti()->id);
    }

    public function test_different_exam_types_and_students_are_independent(): void
    {
        $departement = $this->makeDepartement();
        $a = $this->makeStudent($departement, ['nim' => '111']);
        $b = $this->makeStudent($departement, ['nim' => '222']);
        $sempro = $this->makeExamType('sempro');
        $semhas = $this->makeExamType('semhas', ['kode_ujian' => 'SH']);
        $this->makeExamRegistration($departement, $a, $sempro, ['tanggal_ujian' => '2026-06-01']);
        $this->makeExamRegistration($departement, $a, $semhas, ['tanggal_ujian' => '2026-07-14']);
        $this->makeExamRegistration($departement, $b, $sempro, ['tanggal_ujian' => '2026-07-14']);

        $this->assertSame(0, ExamRegistration::ujianUlang()->count());
    }

    public function test_reported_rows_are_never_ujian_ulang(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $sempro = $this->makeExamType('sempro');
        $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-06-01', 'dilaporkan' => 1]);
        $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-07-14', 'dilaporkan' => 1]);

        $this->assertSame(0, ExamRegistration::ujianUlang()->count());
        $this->assertSame(2, ExamRegistration::tanpaUjianUlang()->count());
    }

    public function test_ujian_ulang_cannot_be_reported(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $sempro = $this->makeExamType('sempro');
        $lama = $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-06-01']);
        $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-07-14']);
        $reportDate = ReportDate::create(['tanggal' => '2026-07-31']);

        $this->expectException(\RuntimeException::class);

        try {
            app(ReportDateController::class)->setReportDate(
                Request::create('', 'PUT', ['report_date_id' => $reportDate->id, 'dilaporkan' => 1]),
                $lama,
            );
        } finally {
            $this->assertNull($lama->fresh()->report_date_id);
            $this->assertFalse($lama->fresh()->dilaporkan);
        }
    }

    public function test_sidang_cascade_skips_ujian_ulang(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        // confirmSidangCascade() memakai exam_type_id 1/2/3 secara hardcode.
        [$sempro, , $sidang] = ExamType::unguarded(fn () => [
            $this->makeExamType('sempro', ['id' => 1]),
            $this->makeExamType('semhas', ['id' => 2, 'kode_ujian' => 'SH']),
            $this->makeExamType('sidang', ['id' => 3, 'kode_ujian' => 'SS']),
        ]);
        $semproLama = $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-06-01']);
        $semproBaru = $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-06-20']);
        $sidangRow = $this->makeExamRegistration($departement, $student, $sidang, ['tanggal_ujian' => '2026-07-14']);
        $reportDate = ReportDate::create(['tanggal' => '2026-07-31']);

        app(ReportDateController::class)->confirmSidangCascade(
            Request::create('', 'PUT', ['report_date_id' => $reportDate->id]),
            $sidangRow,
        );

        $this->assertNull($semproLama->fresh()->report_date_id);
        $this->assertSame($reportDate->id, $semproBaru->fresh()->report_date_id);
        $this->assertSame($reportDate->id, $sidangRow->fresh()->report_date_id);
    }
}
