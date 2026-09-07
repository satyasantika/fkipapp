<?php

namespace App\Filament\Pages;

use App\Models\ExamRegistration;
use App\Models\Lecture;
use App\Models\ReportDate;
use App\Services\ExamPaymentReconciliationService;
use App\Services\ExamPaymentReportService;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Bandingkan exam_payment_reports (List Bayar ASN+Non-ASN, satu tabel yang
 * sama) dengan hitungan fresh dari exam_registrations, per dosen per
 * periode - lihat ExamPaymentReconciliationService untuk logika
 * pembandingnya. Tombol "Sinkronisasi" muncul hanya kalau ada metrik yang
 * tidak cocok, memanggil ulang ExamPaymentReportService::store() (method
 * resmi yang juga dipakai alur "Laporkan Ujian" biasa) untuk SETIAP
 * registrasi ujian dosen itu di periode ini, supaya hasil akhirnya identik
 * dengan kalau ujian-ujian itu baru saja dilaporkan lewat alur normal.
 */
class VerifikasiLaporanHonor extends Page implements HasTable
{
    use Actions\Concerns\InteractsWithActions;
    use Forms\Concerns\InteractsWithForms;
    use Infolists\Concerns\InteractsWithInfolists;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationLabel = 'Verifikasi Laporan Honor';

    protected static ?string $title = 'Verifikasi Laporan Honor';

    protected static string $view = 'filament.pages.verifikasi-laporan-honor';

    public ?int $reportDateId = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('keuangan') ?? false;
    }

    public function mount(): void
    {
        $this->reportDateId = ReportDate::orderByDesc('tanggal')->first()?->id;
    }

    public function getReportDateOptions(): array
    {
        return ReportDate::orderByDesc('tanggal')->get()->mapWithKeys(
            fn (ReportDate $rd) => [$rd->id => Carbon::parse($rd->tanggal)->format('Y-m-d').($rd->deskripsi ? ' - '.$rd->deskripsi : '')]
        )->all();
    }

    public function updatedReportDateId(): void
    {
        $this->resetTable();
    }

    private function service(): ExamPaymentReconciliationService
    {
        return app(ExamPaymentReconciliationService::class);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->actions($this->getTableActions())
            ->heading(fn (): string => $this->summaryHeading());
    }

    protected function getTableQuery(): Builder
    {
        if (! $this->reportDateId) {
            return Lecture::query()->whereRaw('0 = 1');
        }

        $lectureIds = $this->service()->rosterLectureIds($this->reportDateId);

        return Lecture::query()->whereIn('id', $lectureIds);
    }

    protected function summaryHeading(): string
    {
        if (! $this->reportDateId) {
            return 'Pilih periode terlebih dahulu';
        }

        $lectureIds = $this->service()->rosterLectureIds($this->reportDateId);
        $mismatchCount = 0;

        foreach (Lecture::whereIn('id', $lectureIds)->get() as $lecture) {
            if ($this->service()->hasMismatch($this->service()->compare($lecture, $this->reportDateId))) {
                $mismatchCount++;
            }
        }

        $total = count($lectureIds);

        return $mismatchCount > 0
            ? "{$mismatchCount} dari {$total} dosen tidak sesuai"
            : "Semua {$total} dosen sesuai";
    }

    private function metricColumn(string $key, string $label): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make($key)
            ->label($label)
            ->badge()
            ->getStateUsing(function (Lecture $record) use ($key): string {
                $metric = $this->service()->compare($record, $this->reportDateId)[$key];

                return "{$metric['stored']} / {$metric['expected']}";
            })
            ->color(function (Lecture $record) use ($key): string {
                $metric = $this->service()->compare($record, $this->reportDateId)[$key];

                return $metric['match'] ? 'gray' : 'danger';
            });
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nama')
                ->label('Dosen')
                ->searchable(),
            $this->metricColumn('sempro', 'Sempro'),
            $this->metricColumn('semhas', 'Semhas'),
            $this->metricColumn('sidang', 'Sidang'),
            $this->metricColumn('pembimbing1', 'Pemb 1'),
            $this->metricColumn('pembimbing2', 'Pemb 2'),
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->getStateUsing(fn (Lecture $record): string => $this->service()->hasMismatch($this->service()->compare($record, $this->reportDateId))
                    ? 'Tidak Sesuai'
                    : 'Sesuai')
                ->color(fn (Lecture $record): string => $this->service()->hasMismatch($this->service()->compare($record, $this->reportDateId))
                    ? 'danger'
                    : 'success'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('sync')
                ->label('Sinkronisasi')
                ->tooltip('Sinkronisasi')
                ->icon('heroicon-o-arrow-path')
                ->iconButton()
                ->color('warning')
                ->visible(fn (Lecture $record): bool => $this->service()->hasMismatch($this->service()->compare($record, $this->reportDateId)))
                ->requiresConfirmation()
                ->modalDescription('Hitung ulang & simpan List Bayar (ASN/Non-ASN) untuk dosen ini di periode ini, berdasarkan data registrasi ujian yang sebenarnya?')
                ->action(function (Lecture $record): void {
                    $registrations = ExamRegistration::where('report_date_id', $this->reportDateId)
                        ->where(function (Builder $query) use ($record) {
                            $query->where('pembimbing1_id', $record->id)
                                ->orWhere('pembimbing2_id', $record->id)
                                ->orWhere('penguji1_id', $record->id)
                                ->orWhere('penguji2_id', $record->id)
                                ->orWhere('penguji3_id', $record->id);
                        })
                        ->get();

                    $service = app(ExamPaymentReportService::class);

                    foreach ($registrations as $registration) {
                        $service->store($registration->id, $this->reportDateId);
                    }

                    Notification::make()
                        ->title("Sinkronisasi selesai ({$registrations->count()} registrasi ujian dihitung ulang)")
                        ->success()
                        ->send();

                    $this->resetTable();
                }),
        ];
    }
}
