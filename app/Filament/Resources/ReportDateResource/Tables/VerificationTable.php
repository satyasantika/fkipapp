<?php

namespace App\Filament\Resources\ReportDateResource\Tables;

use App\Models\ExamRegistration;
use App\Models\Lecture;
use App\Models\ReportDate;
use App\Services\ExamPaymentReconciliationService;
use App\Services\ExamPaymentReportService;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentView;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

/**
 * Bandingkan exam_payment_reports (List Bayar ASN+Non-ASN, satu tabel yang
 * sama) dengan hitungan fresh dari exam_registrations, per dosen, untuk SATU
 * periode ($this->record) - dipakai di slide-over "Verifikasi Data" pada
 * baris ReportDateResource, di kanan tombol "List Ujian Dilaporkan".
 * Sebelumnya ini halaman menu tersendiri (VerifikasiLaporanHonor) dengan
 * selector periode manual - dipindah ke sini supaya periodenya otomatis
 * dari record yang diklik, konsisten dengan NotReportedTable/
 * PaymentSectionTable yang sudah ada.
 *
 * Lihat ExamPaymentReconciliationService untuk logika pembandingnya.
 */
class VerificationTable extends Component implements Actions\Contracts\HasActions, Forms\Contracts\HasForms, Infolists\Contracts\HasInfolists, HasTable
{
    use Actions\Concerns\InteractsWithActions;
    use Forms\Concerns\InteractsWithForms;
    use Infolists\Concerns\InteractsWithInfolists;
    use InteractsWithTable;

    public ReportDate $record;

    public bool $belumSesuaiOnly = false;

    private bool $filterHookRegistered = false;

    public function render()
    {
        return view('filament.resources.report-date-resource.tables.verification-embedded');
    }

    private function service(): ExamPaymentReconciliationService
    {
        return app(ExamPaymentReconciliationService::class);
    }

    public function table(Table $table): Table
    {
        $this->registerFilterHook();

        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->actions($this->getTableActions())
            ->heading(fn (): string => $this->summaryHeading());
    }

    /**
     * Tombol icon "Tidak Sesuai saja" ditaruh persis di sebelah kotak
     * pencarian bawaan Filament (TOOLBAR_SEARCH_BEFORE) - pola sama yang
     * sudah dipakai ListExamRegistrations::registerDilaporkanFilterHook()
     * (dan sudah dibuktikan aman untuk komponen Livewire embedded seperti
     * ini, bukan cuma untuk halaman resource penuh - scopes: static::class
     * benar-benar resolve ke instance komponen ini saat render Livewire).
     */
    protected function registerFilterHook(): void
    {
        if ($this->filterHookRegistered) {
            return;
        }

        $this->filterHookRegistered = true;

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_SEARCH_BEFORE,
            fn (): string => view('filament.resources.report-date-resource.tables.verification-filter-button', [
                'active' => $this->belumSesuaiOnly,
            ])->render(),
            scopes: static::class,
        );
    }

    public function toggleBelumSesuaiOnly(): void
    {
        $this->belumSesuaiOnly = ! $this->belumSesuaiOnly;
        $this->resetTable();
    }

    protected function getTableQuery(): Builder
    {
        $lectureIds = $this->service()->rosterLectureIds($this->record->id);

        if ($this->belumSesuaiOnly) {
            $lectureIds = collect($lectureIds)
                ->filter(fn (int $id) => $this->service()->hasMismatch(
                    $this->service()->compare(Lecture::find($id), $this->record->id)
                ))
                ->values()
                ->all();
        }

        return Lecture::query()->whereIn('id', $lectureIds);
    }

    protected function summaryHeading(): string
    {
        $lectureIds = $this->service()->rosterLectureIds($this->record->id);
        $mismatchCount = 0;

        foreach (Lecture::whereIn('id', $lectureIds)->get() as $lecture) {
            if ($this->service()->hasMismatch($this->service()->compare($lecture, $this->record->id))) {
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
                $metric = $this->service()->compare($record, $this->record->id)[$key];

                return "{$metric['stored']} / {$metric['expected']}";
            })
            ->color(function (Lecture $record) use ($key): string {
                $metric = $this->service()->compare($record, $this->record->id)[$key];

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
                ->getStateUsing(fn (Lecture $record): string => $this->service()->hasMismatch($this->service()->compare($record, $this->record->id))
                    ? 'Tidak Sesuai'
                    : 'Sesuai')
                ->color(fn (Lecture $record): string => $this->service()->hasMismatch($this->service()->compare($record, $this->record->id))
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
                ->visible(fn (Lecture $record): bool => $this->service()->hasMismatch($this->service()->compare($record, $this->record->id)))
                ->requiresConfirmation()
                ->modalDescription('Hitung ulang & simpan List Bayar (ASN/Non-ASN) untuk dosen ini di periode ini, berdasarkan data registrasi ujian yang sebenarnya?')
                ->action(function (Lecture $record): void {
                    $registrations = ExamRegistration::where('report_date_id', $this->record->id)
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
                        $service->store($registration->id, $this->record->id);
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
