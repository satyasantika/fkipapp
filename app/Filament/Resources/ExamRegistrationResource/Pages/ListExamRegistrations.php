<?php

namespace App\Filament\Resources\ExamRegistrationResource\Pages;

use App\Filament\Resources\ExamRegistrationResource;
use App\Models\Departement;
use App\Services\SintesysSyncService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\Tables\View\TablesRenderHook;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ListExamRegistrations extends ListRecords
{
    protected static string $resource = ExamRegistrationResource::class;

    /**
     * 'semua' | 'sudah' | 'belum'.
     */
    public string $dilaporkanFilter = 'semua';

    public int $calendarMonth;

    public int $calendarYear;

    public ?string $selectedDate = null;

    public ?int $syncDepartementId = null;

    /**
     * @var array{items?: array<int, array<string, mixed>>, summary?: array<string, int>}
     */
    public array $syncPreview = [];

    /**
     * 'pick' | 'preview'.
     */
    public string $syncStep = 'pick';

    private bool $dilaporkanFilterHookRegistered = false;

    private bool $calendarHookRegistered = false;

    public function mount(): void
    {
        parent::mount();

        $this->calendarMonth = now()->month;
        $this->calendarYear = now()->year;
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
        $this->registerCalendarHook();

        return parent::table($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => match ($this->dilaporkanFilter) {
                'sudah' => $query->where('dilaporkan', true),
                'belum' => $query->where('dilaporkan', false),
                default => $query,
            })
            ->modifyQueryUsing(fn (Builder $query): Builder => $this->selectedDate
                ? $query->whereDate('tanggal_ujian', $this->selectedDate)
                : $query);
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

    /**
     * Kalender jumlah ujian per tanggal, ditaruh via render hook resmi
     * Filament (RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE - dirender oleh
     * template standar list-records.blade.php tepat sebelum {{ $this->table }},
     * lihat vendor/filament/filament/resources/views/resources/pages/
     * list-records.blade.php) supaya tidak perlu override seluruh view
     * halaman. scopes: static::class aman di sini dengan alasan yang sama
     * seperti registerDilaporkanFilterHook() di atas.
     */
    protected function registerCalendarHook(): void
    {
        if ($this->calendarHookRegistered) {
            return;
        }

        $this->calendarHookRegistered = true;

        FilamentView::registerRenderHook(
            PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE,
            fn (): string => view('filament.resources.exam-registration-resource.calendar', [
                'month' => $this->calendarMonth,
                'year' => $this->calendarYear,
                'selectedDate' => $this->selectedDate,
                'days' => $this->getCalendarDays(),
                'canSync' => $this->canSync(),
                'departements' => $this->canSync() ? Departement::orderBy('nama')->get() : collect(),
                'syncStep' => $this->syncStep,
                'syncPreview' => $this->syncPreview,
                'syncDepartementId' => $this->syncDepartementId,
                'isJurusan' => auth()->user()?->hasRole('jurusan') ?? false,
            ])->render(),
            scopes: static::class,
        );
    }

    /**
     * Hitung total/sudah/belum per tanggal untuk bulan+tahun kalender saat
     * ini, dari query dasar resource yang sama (supaya scope jurusan tetap
     * dihormati) - BUKAN dari query tabel yang sudah difilter status/tanggal,
     * supaya kalender tetap menunjukkan semua ujian bulan itu walau tabel
     * di bawahnya sedang difilter.
     */
    /**
     * jurusan melihat rincian jenis ujian (total/sempro/semhas/sidang) di
     * kalender - mereka yang menjadwalkan mahasiswa lewat tahap-tahap itu.
     * Role lain (keuangan/admin) melihat rincian sudah/belum dilaporkan -
     * itu yang relevan untuk pemantauan pelaporan honor mereka.
     */
    protected function getCalendarDays(): array
    {
        $start = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $rows = ExamRegistrationResource::getEloquentQuery()
            ->join('exam_types', 'exam_types.id', '=', 'exam_registrations.exam_type_id')
            ->whereBetween('exam_registrations.tanggal_ujian', [$start->toDateString(), $end->toDateString()])
            ->get(['exam_registrations.tanggal_ujian', 'exam_registrations.dilaporkan', 'exam_types.singkat_ujian']);

        $days = [];

        foreach ($rows as $row) {
            $date = Carbon::parse($row->tanggal_ujian)->toDateString();
            $days[$date] ??= ['total' => 0, 'sudah' => 0, 'belum' => 0, 'sempro' => 0, 'semhas' => 0, 'sidang' => 0];
            $days[$date]['total']++;
            $days[$date][$row->dilaporkan ? 'sudah' : 'belum']++;

            if (isset($days[$date][$row->singkat_ujian])) {
                $days[$date][$row->singkat_ujian]++;
            }
        }

        return $days;
    }

    public function goToPreviousMonth(): void
    {
        $date = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarMonth = $date->month;
        $this->calendarYear = $date->year;
    }

    public function goToNextMonth(): void
    {
        $date = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarMonth = $date->month;
        $this->calendarYear = $date->year;
    }

    public function goToCurrentMonth(): void
    {
        $this->calendarMonth = now()->month;
        $this->calendarYear = now()->year;
    }

    public function selectCalendarDate(string $date): void
    {
        $this->selectedDate = $this->selectedDate === $date ? null : $date;
        $this->resetTable();
    }

    public function clearSelectedDate(): void
    {
        $this->selectedDate = null;
        $this->resetTable();
    }

    /**
     * Hanya jurusan (kode_prodi = departemen sendiri) dan keuangan (pilih
     * departemen lewat dropdown) yang boleh menyinkronkan data dari
     * Sintesys - keputusan eksplisit dari pemilik aplikasi.
     */
    public function canSync(): bool
    {
        return auth()->user()?->hasRole(['jurusan', 'keuangan']) ?? false;
    }

    public function openSyncModal(): void
    {
        $this->syncStep = 'pick';
        $this->syncPreview = [];
        $this->syncDepartementId = auth()->user()?->hasRole('jurusan')
            ? auth()->user()->departement_id
            : null;

        $this->dispatch('open-modal', id: 'sync-exams');
    }

    /**
     * Tarik data Sintesys untuk bulan/tahun kalender yang sedang tampil,
     * lalu analisis (belum menulis ke DB sama sekali - lihat
     * SintesysSyncService::analyze()/analyzeAllDepartments()). Hasilnya
     * disimpan di $syncPreview supaya confirmSync() tidak perlu fetch API
     * dua kali.
     *
     * jurusan -> satu departemen (miliknya sendiri). keuangan -> SEMUA
     * jurusan sekaligus (tidak perlu pilih), karena keuangan tidak terikat
     * satu departemen.
     */
    public function loadSyncPreview(): void
    {
        if (! $this->canSync()) {
            return;
        }

        $start = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        try {
            $service = app(SintesysSyncService::class);

            if (auth()->user()?->hasRole('jurusan')) {
                $rows = $service->fetchExams((string) $this->syncDepartementId, $start->toDateString(), $end->toDateString());
                $this->syncPreview = $service->analyze($rows, $this->syncDepartementId);
            } else {
                $this->syncPreview = $service->analyzeAllDepartments($start->toDateString(), $end->toDateString());
            }

            $this->syncStep = 'preview';
        } catch (\Throwable $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }

    public function backToSyncPick(): void
    {
        $this->syncStep = 'pick';
    }

    public function confirmSync(): void
    {
        if (! $this->canSync() || empty($this->syncPreview['items'])) {
            return;
        }

        $summary = app(SintesysSyncService::class)->commit($this->syncPreview['items']);

        Notification::make()
            ->title('Sinkronisasi selesai')
            ->body("Dibuat: {$summary['dibuat']}, diperbarui: {$summary['diperbarui']}, mahasiswa baru: {$summary['mahasiswa_baru']}, dosen baru: {$summary['dosen_baru']}, dilewati: {$summary['dilewati_jenis_tidak_dikenal']}.")
            ->success()
            ->send();

        $this->syncStep = 'pick';
        $this->syncPreview = [];
        $this->dispatch('close-modal', id: 'sync-exams');
        $this->resetTable();
    }
}
