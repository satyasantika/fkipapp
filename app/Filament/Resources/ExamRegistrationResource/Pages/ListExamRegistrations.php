<?php

namespace App\Filament\Resources\ExamRegistrationResource\Pages;

use App\Filament\Resources\ExamRegistrationResource;
use App\Models\ExamRegistration;
use App\Models\Student;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ListExamRegistrations extends ListRecords
{
    protected static string $resource = ExamRegistrationResource::class;

    /**
     * 'semua' | 'sudah' | 'belum'.
     */
    public string $dilaporkanFilter = 'semua';

    private bool $dilaporkanFilterHookRegistered = false;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Registrasi Ujian')
                ->icon('heroicon-o-plus')
                ->using(function (array $data): Model {
                    $student = Student::findOrFail($data['student_id']);

                    $record = ExamRegistration::updateOrCreate([
                        'departement_id' => $student->departement_id,
                        'student_id' => $data['student_id'],
                        'exam_type_id' => $data['exam_type_id'] ?? null,
                        'ujian_ke' => $data['ujian_ke'] ?? null,
                    ], [
                        'tanggal_ujian' => $data['tanggal_ujian'] ?? null,
                        'waktu_mulai' => $data['waktu_mulai'] ?? null,
                        'waktu_akhir' => $data['waktu_akhir'] ?? null,
                        'ruangan' => $data['ruangan'] ?? null,
                        'judul_penelitian' => $data['judul_penelitian'] ?? null,
                        'ipk' => $data['ipk'] ?? null,
                        'penguji1_id' => $student->penguji1_id,
                        'penguji2_id' => $student->penguji2_id,
                        'penguji3_id' => $student->penguji3_id,
                        'pembimbing1_id' => $student->pembimbing1_id,
                        'pembimbing2_id' => $student->pembimbing2_id,
                        'ketuapenguji_id' => $student->ketuapenguji_id,
                    ]);

                    if ($dateColumn = ExamRegistrationResource::examTypeDateColumn($data['exam_type_id'] ?? null)) {
                        $student->update([$dateColumn => $data['tanggal_ujian'] ?? null]);
                    }

                    return $record;
                }),
        ];
    }

    /**
     * Filter "Sudah"/"Belum"/"Semua" dibuat manual (properti + tombol lewat
     * render hook), bukan Table::filters() bawaan Filament, supaya tombolnya
     * bisa ditaruh PERSIS di sebelah kiri kotak pencarian (TOOLBAR_SEARCH_BEFORE
     * dirender tepat sebelum <x-filament-tables::search-field> di baris toolbar
     * yang sama - lihat vendor/filament/tables/resources/views/index.blade.php).
     *
     * scopes: static::class DIJAMIN aman di sini (tidak bocor ke tabel Filament
     * lain) - beda dengan blade biasa (static::class di situ selalu resolve ke
     * Illuminate\Filesystem\Filesystem, sudah dicek), di dalam render Livewire
     * static::class benar-benar resolve ke instance komponen ini (dites langsung
     * lewat Livewire::test() + FilamentView::registerRenderHook scoped).
     */
    public function table(Table $table): Table
    {
        $this->registerDilaporkanFilterHook();

        return parent::table($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => match ($this->dilaporkanFilter) {
                'sudah' => $query->where('dilaporkan', true),
                'belum' => $query->where('dilaporkan', false),
                default => $query,
            });
    }

    protected function registerDilaporkanFilterHook(): void
    {
        if ($this->dilaporkanFilterHookRegistered) {
            return;
        }

        $this->dilaporkanFilterHookRegistered = true;

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_SEARCH_BEFORE,
            fn (): string => view('filament.resources.exam-registration-resource.dilaporkan-filter', [
                'current' => $this->dilaporkanFilter,
            ])->render(),
            scopes: static::class,
        );
    }

    public function setDilaporkanFilter(string $value): void
    {
        $this->dilaporkanFilter = $value;
        $this->resetTable();
    }
}
