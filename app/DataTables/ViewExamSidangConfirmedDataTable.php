<?php

namespace App\DataTables;

use App\DataTables\Concerns\FiltersExamRegistrationNames;
use App\Models\ExamRegistration;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

/**
 * Roster mahasiswa yang ujian sidangnya sudah dilaporkan ke periode manapun
 * (report_date_id sidang tidak null) — dipakai staf keuangan untuk menyusul
 * (cascade) sempro/semhas mahasiswa yang sama yang belum pernah dilaporkan ke
 * periode manapun, ke periode YANG SAMA dengan sidangnya (bukan periode
 * tertentu — halaman ini global lintas periode).
 */
class ViewExamSidangConfirmedDataTable extends DataTable
{
    use FiltersExamRegistrationNames;

    private const EXAM_TYPE_SIDANG = 3;

    private const EXAM_TYPE_BELUM_DILAPORKAN = [1, 2]; // sempro, semhas

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        // applyExamRegistrationNameColumns() dipanggil DULUAN karena ia juga
        // meregister editColumn('ujian', ...) versi polos — kalau dipanggil
        // belakangan, itu akan menimpa balik badge sempro/semhas di bawah ini.
        $dataTable = $this->applyExamRegistrationNameColumns(new EloquentDataTable($query))
            ->addColumn('action', function ($row) {
                $pending = $this->pendingSiblings($row);

                if ($pending->isEmpty()) {
                    return '<span class="badge bg-secondary">lengkap</span>';
                }

                $action = ' <form action="'.route('reportdates.confirmsidangcascade', $row->id).'" method="POST">';
                $action .= '<input type="hidden" name="_token" value="'.csrf_token().'">';
                $action .= '<input type="hidden" name="_method" value="PUT">';
                $action .= '<button type="submit" class="btn btn-success btn-sm" title="Tambahkan '.$pending->count().' data ujian lain yang belum dilaporkan">+ '.$pending->count().'</button> </form> ';

                return $action;
            })
            ->editColumn('dilaporkan', function ($row) {
                return $row->dilaporkan ? 'sudah' : 'belum';
            })
            ->editColumn('ujian', function ($row) {
                $badges = '<span class="badge bg-primary">'.($row->ujian ?? '').'</span>';

                foreach ($this->siblingsByType($row) as $exam_type_id => $sibling) {
                    if (! is_null($sibling->report_date_id)) {
                        continue;
                    }
                    $badges .= $exam_type_id === 1
                        ? ' <span class="badge bg-secondary" title="sempro belum dilaporkan">sempro</span>'
                        : ' <span class="badge bg-info text-dark" title="semhas belum dilaporkan">semhas</span>';
                }

                return $badges;
            })
            ->editColumn('pembimbing1_nama', fn ($row) => $this->markIfChanged($row, 'pembimbing1_id', $row->pembimbing1_nama))
            ->editColumn('pembimbing2_nama', fn ($row) => $this->markIfChanged($row, 'pembimbing2_id', $row->pembimbing2_nama))
            ->editColumn('penguji1_nama', fn ($row) => $this->markIfChanged($row, 'penguji1_id', $row->penguji1_nama))
            ->editColumn('penguji2_nama', fn ($row) => $this->markIfChanged($row, 'penguji2_id', $row->penguji2_nama))
            ->editColumn('penguji3_nama', fn ($row) => $this->markIfChanged($row, 'penguji3_id', $row->penguji3_nama))
            ->rawColumns(['action', 'ujian', 'pembimbing1_nama', 'pembimbing2_nama', 'penguji1_nama', 'penguji2_nama', 'penguji3_nama']);

        return $dataTable->setRowId('id');
    }

    /**
     * Semua registrasi ujian (sempro/semhas/sidang) mahasiswa yang sama dengan
     * baris ini, termasuk baris ini sendiri.
     */
    private function siblingsAll(ExamRegistration $row)
    {
        return $row->student->examregistrations;
    }

    /**
     * sempro/semhas mahasiswa ini yang belum pernah dilaporkan ke periode manapun.
     */
    private function pendingSiblings(ExamRegistration $row)
    {
        return $this->siblingsAll($row)
            ->whereIn('exam_type_id', self::EXAM_TYPE_BELUM_DILAPORKAN)
            ->whereNull('report_date_id');
    }

    /**
     * sempro/semhas mahasiswa ini (kalau ada), untuk badge kolom ujian.
     *
     * @return array<int, ExamRegistration>
     */
    private function siblingsByType(ExamRegistration $row): array
    {
        $result = [];
        foreach (self::EXAM_TYPE_BELUM_DILAPORKAN as $exam_type_id) {
            $sibling = $this->siblingsAll($row)->firstWhere('exam_type_id', $exam_type_id);
            if ($sibling) {
                $result[$exam_type_id] = $sibling;
            }
        }

        return $result;
    }

    /**
     * Tandai nama pembimbing/penguji kalau nilainya pernah berbeda di antara
     * sempro/semhas/sidang mahasiswa ini (komposisi pembimbing/penguji berubah
     * antar tahap ujian).
     */
    private function markIfChanged(ExamRegistration $row, string $column, ?string $displayName): string
    {
        $name = $displayName ?? '';

        $changed = $this->siblingsAll($row)->pluck($column)->filter()->unique()->count() > 1;

        if (! $changed) {
            return $name;
        }

        return $name.' <span class="badge bg-warning text-dark" title="Berbeda antara sempro/semhas/sidang">≠</span>';
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ExamRegistration $model): QueryBuilder
    {
        $query = $this->eagerLoadExamRegistrationNames($model)
            ->with(['student.examregistrations' => fn ($q) => $q->whereIn('exam_type_id', [1, 2, self::EXAM_TYPE_SIDANG])])
            ->where('exam_type_id', self::EXAM_TYPE_SIDANG)
            ->whereNotNull('report_date_id');

        return $query->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('viewexamsidangconfirmed-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax(url()->current())
                    ->orderBy(3, 'asc')
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        Button::make('reset'),
                        Button::make('reload'),
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(90)
                  ->addClass('text-center'),
            Column::make('dilaporkan')->title('lapor?'),
            Column::make('ujian'),
            Column::make('tanggal_ujian')->title('tgl. sidang'),
            Column::make('nim'),
            Column::make('mahasiswa'),
            Column::make('pembimbing1_nama')->title('Pemb.1'),
            Column::make('pembimbing2_nama')->title('Pemb.2'),
            Column::make('penguji1_nama')->title('Peng.1'),
            Column::make('penguji2_nama')->title('Peng.2'),
            Column::make('penguji3_nama')->title('Peng.3'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ViewExamSidangConfirmed_'.date('YmdHis');
    }
}
