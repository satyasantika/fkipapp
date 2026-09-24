<?php

namespace Tests\Unit;

use App\Models\ExamRegistration;
use App\Models\Lecture;
use PHPUnit\Framework\TestCase;

class ExamRegistrationPengujiBerurutanTest extends TestCase
{
    private function lecture(int $id, string $nama): Lecture
    {
        return (new Lecture)->forceFill(['id' => $id, 'nama' => $nama]);
    }

    private function registration(?Lecture $ketua, Lecture $pembimbing1): ExamRegistration
    {
        $penguji1 = $this->lecture(10, 'Penguji Satu');
        $penguji2 = $this->lecture(11, 'Penguji Dua');
        $pembimbing2 = $this->lecture(21, 'Pembimbing Dua');

        return (new ExamRegistration)
            ->forceFill([
                'ketuapenguji_id' => $ketua?->id,
                'penguji1_id' => $penguji1->id,
                'penguji2_id' => $penguji2->id,
                'penguji3_id' => null,
                'pembimbing1_id' => $pembimbing1->id,
                'pembimbing2_id' => $pembimbing2->id,
            ])
            ->setRelation('ketuapenguji', $ketua)
            ->setRelation('penguji1', $penguji1)
            ->setRelation('penguji2', $penguji2)
            ->setRelation('penguji3', null)
            ->setRelation('pembimbing1', $pembimbing1)
            ->setRelation('pembimbing2', $pembimbing2);
    }

    public function test_ketua_penguji_comes_first(): void
    {
        $registration = $this->registration($this->lecture(1, 'Ketua'), $this->lecture(20, 'Pembimbing Satu'));

        $this->assertSame(['Ketua', 'Penguji Satu', 'Penguji Dua'], $registration->pengujiBerurutan()->pluck('nama')->all());
    }

    public function test_ketua_is_skipped_when_ketua_is_pembimbing(): void
    {
        $pembimbing1 = $this->lecture(20, 'Pembimbing Satu');
        $registration = $this->registration($pembimbing1, $pembimbing1);

        $this->assertSame(['Penguji Satu', 'Penguji Dua'], $registration->pengujiBerurutan()->pluck('nama')->all());
    }

    public function test_without_ketua_only_penguji_are_listed(): void
    {
        $registration = $this->registration(null, $this->lecture(20, 'Pembimbing Satu'));

        $this->assertSame(['Penguji Satu', 'Penguji Dua'], $registration->pengujiBerurutan()->pluck('nama')->all());
    }
}
