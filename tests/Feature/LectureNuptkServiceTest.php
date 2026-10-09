<?php

namespace Tests\Feature;

use App\Models\Lecture;
use App\Services\LectureNuptkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\SeedsExamFixtures;
use Tests\TestCase;

class LectureNuptkServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsExamFixtures;

    public function test_parse_accepts_excel_tabs_semicolons_and_spaces(): void
    {
        $rows = (new LectureNuptkService)->parse(
            "NIDN\tNUPTK\tNama\tKode\n"
            ."0011\t9001\tDr. Ani, M.Pd.\t3\n"
            ."\n"
            ."0022;9002\n"
            ."0033   9003\n"
        );

        $this->assertCount(3, $rows);
        $this->assertSame(['baris' => 2, 'nidn' => '0011', 'nuptk' => '9001', 'nama' => 'Dr. Ani, M.Pd.', 'departement_id' => '3'], $rows[0]);
        $this->assertSame('9002', $rows[1]['nuptk']);
        $this->assertSame('9003', $rows[2]['nuptk']);
    }

    public function test_apply_updates_by_nidn_creates_new_and_skips_conflicts(): void
    {
        $departement = $this->makeDepartement();
        $ani = $this->makeLecture($departement, ['nidn' => '0011']);
        $this->makeLecture($departement, ['nidn' => '0099', 'nuptk' => '9999']);

        $hasil = (new LectureNuptkService)->apply([
            ['baris' => 1, 'nidn' => '0011', 'nuptk' => '9001'],
            ['baris' => 2, 'nidn' => '0022', 'nuptk' => '9002', 'nama' => 'Budi', 'departement_id' => (string) $departement->id],
            ['baris' => 3, 'nidn' => '0033', 'nuptk' => '9999'],
            ['baris' => 4, 'nidn' => '0044', 'nuptk' => '9004'],
            ['baris' => 5, 'nidn' => '0055', 'nuptk' => null],
        ]);

        $this->assertSame(1, $hasil['diperbarui']);
        $this->assertSame(1, $hasil['dibuat']);
        $this->assertCount(3, $hasil['dilewati']);
        $this->assertSame('9001', $ani->fresh()->nuptk);
        $this->assertDatabaseHas('lectures', ['nidn' => '0022', 'nuptk' => '9002', 'nama' => 'Budi']);
        $this->assertDatabaseMissing('lectures', ['nidn' => '0044']);
    }

    public function test_fill_from_sintesys_only_fills_empty_nuptk_without_creating(): void
    {
        $departement = $this->makeDepartement();
        $kosong = $this->makeLecture($departement, ['nidn' => '0011']);
        $terisi = $this->makeLecture($departement, ['nidn' => '0022', 'nuptk' => 'LAMA']);

        Http::fake(['*' => Http::response(['data' => [[
            'penguji' => [
                ['urutan' => 1, 'nidn' => '0011', 'nuptk' => '9001'],
                ['urutan' => 2, 'nidn' => '0022', 'nuptk' => '9002'],
                ['urutan' => 3, 'nidn' => '0033', 'nuptk' => '9003'],
            ],
        ]]])]);

        $hasil = (new LectureNuptkService)->fillFromSintesys('2026-01-01', '2026-10-09');

        $this->assertSame(1, $hasil['diperbarui']);
        $this->assertSame(0, $hasil['dibuat']);
        $this->assertSame('9001', $kosong->fresh()->nuptk);
        $this->assertSame('LAMA', $terisi->fresh()->nuptk);
        $this->assertDatabaseMissing('lectures', ['nidn' => '0033']);

        // Rentang 2026-01-01..2026-10-09 (282 hari) dipecah jadi 2 request.
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request['tanggal_mulai'] === '2026-06-30' && $request['tanggal_selesai'] === '2026-10-09');
    }
}
