<?php

namespace Tests\Feature;

use App\Models\ExamRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\SeedsExamFixtures;
use Tests\TestCase;

class HapusUjianDobelSinkronTest extends TestCase
{
    use RefreshDatabase;
    use SeedsExamFixtures;

    private array $ids = [];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $departement = $this->makeDepartement();
        $examType = $this->makeExamType('sempro');
        $dobel = $this->makeStudent($departement, ['nim' => '111']);
        $ulang = $this->makeStudent($departement, ['nim' => '222']);
        $tunggal = $this->makeStudent($departement, ['nim' => '333']);

        $this->ids = [
            'dilaporkan' => $this->makeExamRegistration($departement, $dobel, $examType, ['tanggal_ujian' => '2026-07-14', 'dilaporkan' => 1])->id,
            'dobel' => $this->makeExamRegistration($departement, $dobel, $examType, ['tanggal_ujian' => '2026-07-14', 'ujian_ke' => 2])->id,
            'ulang_lama' => $this->makeExamRegistration($departement, $ulang, $examType, ['tanggal_ujian' => '2026-06-01', 'dilaporkan' => 1])->id,
            'ulang_baru' => $this->makeExamRegistration($departement, $ulang, $examType, ['tanggal_ujian' => '2026-07-14', 'ujian_ke' => 2])->id,
            'tunggal' => $this->makeExamRegistration($departement, $tunggal, $examType, ['tanggal_ujian' => '2026-07-14'])->id,
        ];
    }

    public function test_dry_run_deletes_nothing(): void
    {
        $this->artisan('exam:hapus-dobel-sinkron')->assertSuccessful();

        $this->assertSame(5, ExamRegistration::count());
    }

    public function test_force_deletes_only_unreported_same_date_duplicate(): void
    {
        $this->artisan('exam:hapus-dobel-sinkron', ['--force' => true, '--no-interaction' => true])->assertSuccessful();

        $this->assertDatabaseMissing('exam_registrations', ['id' => $this->ids['dobel']]);

        foreach (['dilaporkan', 'ulang_lama', 'ulang_baru', 'tunggal'] as $key) {
            $this->assertDatabaseHas('exam_registrations', ['id' => $this->ids[$key]]);
        }

        $this->assertSame(2, ExamRegistration::where('dilaporkan', true)->count());

        $files = Storage::disk('local')->files('backup');
        $this->assertCount(1, $files);
        $this->assertSame([$this->ids['dobel']], array_column(json_decode(Storage::disk('local')->get($files[0]), true), 'id'));
    }
}
