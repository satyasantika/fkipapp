<?php

namespace App\Filament\Resources\ReportDateResource\Tables;

use App\Http\Controllers\ReportDateController;
use App\Models\ExamRegistration;
use App\Models\ReportDate;
use App\Models\Student;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Livewire\Component;

/**
 * Roster mahasiswa (satu mahasiswa = satu kartu, bukan satu baris per
 * ujian) yang punya minimal satu ujian belum dilaporkan ke periode
 * manapun. Kartu menampilkan badge tiap jenis ujian yang masih pending
 * (sempro/semhas/sidang, warna beda per jenis) - klik badge itu untuk
 * menambahkan ujian itu saja ke laporan, atau badge "+N semua ujian"
 * (muncul kalau mahasiswa itu punya >1 ujian pending) untuk menambahkan
 * semuanya sekaligus - beserta nama pembimbing/penguji dari ujian
 * terbarunya.
 *
 * Pencarian & filter dibuat manual (bukan lewat Table::filters()/
 * ->searchable() bawaan Filament) supaya tombol filter bisa ditaruh
 * persis di samping kiri kotak pencarian (lihat not-reported-table.blade.php) -
 * layout toolbar bawaan Filament tidak punya slot untuk itu.
 *
 * Filter "Sudah Sidang" MENYARING MAHASISWA (siapa saja yang salah satu
 * ujian pending-nya sidang), bukan menyaring badge ujian - sempro/semhas
 * mahasiswa itu tetap ikut tampil di kartunya. Pencarian menyasar
 * NIM/nama mahasiswa maupun nama pembimbing/penguji dari ujian pending-nya.
 *
 * Kartu diwarnai danger kalau mahasiswa itu sidangnya sudah pernah
 * dilaporkan (report_date_id terisi) TAPI masih ada sempro/semhas yang
 * baru muncul belum dilaporkan sama sekali - urutan yang janggal dan
 * perlu perhatian staf keuangan.
 *
 * Kombinasi 4 interface+trait ini meniru persis Filament\Widgets\TableWidget
 * bawaan (lihat vendor/filament/widgets/src/TableWidget.php) - HasTable saja
 * TIDAK cukup karena tabel Filament dibangun di atas forms/actions/infolists.
 */
class NotReportedTable extends Component implements Actions\Contracts\HasActions, Forms\Contracts\HasForms, Infolists\Contracts\HasInfolists, HasTable
{
    use Actions\Concerns\InteractsWithActions;
    use Forms\Concerns\InteractsWithForms;
    use Infolists\Concerns\InteractsWithInfolists;
    use InteractsWithTable;

    public ReportDate $record;

    public bool $sudahSidangOnly = false;

    public string $studentSearch = '';

    private const EXAM_TYPE_SIDANG = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->contentGrid(['sm' => 2, 'md' => 3, 'xl' => 4])
            ->columns($this->getTableColumns())
            ->defaultSort('latest_ujian', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $query = Student::query()
            ->whereHas('examregistrations', fn (Builder $q) => $q->whereNull('report_date_id'))
            ->with([
                'examregistrations' => fn ($q) => $q->whereNull('report_date_id')
                    ->with(['exam_type', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2', 'penguji3'])
                    ->orderByDesc('tanggal_ujian'),
            ])
            ->withMax(['examregistrations as latest_ujian' => fn ($q) => $q->whereNull('report_date_id')], 'tanggal_ujian')
            ->withExists(['examregistrations as has_reported_sidang' => fn ($q) => $q
                ->where('exam_type_id', self::EXAM_TYPE_SIDANG)
                ->whereNotNull('report_date_id'),
            ]);

        if ($this->sudahSidangOnly) {
            $query->whereHas(
                'examregistrations',
                fn ($q) => $q->whereNull('report_date_id')->where('exam_type_id', self::EXAM_TYPE_SIDANG)
            );
        }

        if (filled($this->studentSearch)) {
            $search = $this->studentSearch;

            $query->where(function (Builder $q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhereHas('examregistrations', function (Builder $eq) use ($search) {
                        $eq->whereNull('report_date_id')->where(function (Builder $lecturerQuery) use ($search) {
                            $lecturerQuery->whereHas('pembimbing1', fn ($pq) => $pq->where('nama', 'like', "%{$search}%"))
                                ->orWhereHas('pembimbing2', fn ($pq) => $pq->where('nama', 'like', "%{$search}%"))
                                ->orWhereHas('penguji1', fn ($pq) => $pq->where('nama', 'like', "%{$search}%"))
                                ->orWhereHas('penguji2', fn ($pq) => $pq->where('nama', 'like', "%{$search}%"))
                                ->orWhereHas('penguji3', fn ($pq) => $pq->where('nama', 'like', "%{$search}%"));
                        });
                    });
            });
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            // Dibungkus Layout\Stack supaya Table::$hasColumnsLayout jadi true -
            // tanpa itu ->contentGrid() diam-diam tidak berpengaruh sama sekali
            // (index.blade.php Filament cuma memakai grid kalau ada minimal satu
            // kolom Layout, kolom datar seperti ViewColumn saja tidak dihitung).
            Tables\Columns\Layout\Stack::make([
                Tables\Columns\ViewColumn::make('card')
                    ->label('')
                    ->view('filament.resources.report-date-resource.tables.student-card'),
            ]),
        ];
    }

    public function toggleSudahSidang(): void
    {
        $this->sudahSidangOnly = ! $this->sudahSidangOnly;
        $this->resetTable();
    }

    public function updatedStudentSearch(): void
    {
        $this->resetTable();
    }

    public function assignSingle(int $examRegistrationId): void
    {
        $record = ExamRegistration::findOrFail($examRegistrationId);

        $request = Request::create('', 'PUT', [
            'report_date_id' => $this->record->id,
            'dilaporkan' => 1,
        ]);

        try {
            app(ReportDateController::class)->setReportDate($request, $record);
            Notification::make()->title('Ditambahkan ke laporan')->success()->send();
        } catch (\RuntimeException $e) {
            Notification::make()->title($e->getMessage())->warning()->send();
        }

        $this->resetTable();
    }

    public function assignAllForStudent(int $examRegistrationId): void
    {
        $record = ExamRegistration::findOrFail($examRegistrationId);

        $request = Request::create('', 'PUT', [
            'report_date_id' => $this->record->id,
        ]);

        try {
            app(ReportDateController::class)->confirmSidangCascade($request, $record);
            Notification::make()->title('Berhasil menambahkan data ujian ke laporan')->success()->send();
        } catch (\RuntimeException $e) {
            Notification::make()->title($e->getMessage())->warning()->send();
        }

        $this->resetTable();
    }

    public function render()
    {
        return view('filament.resources.report-date-resource.tables.not-reported-table');
    }
}
