<?php

namespace App\DataTables;

use App\Models\ExamPaymentReport;
use App\Models\Lecture;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ViewExamPaymentReportsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        // total_honor/potong_pajak/honor_dibayar dulu kolom SQL view, sekarang
        // accessor PHP di ExamPaymentReport — direplikasi lagi di sini sebagai
        // raw SQL (bukan lewat relasi) supaya kolom finansial ini tetap bisa
        // disortir tanpa full-collection-sort. persen_pajak disederhanakan jadi
        // CASE 2 cabang karena cabang golongan=3 di accessor aslinya memang tidak
        // pernah menyala (lihat komentar bug npwp=NULL di ExamPaymentReport).
        $totalHonorSql = '(honor_pembimbing*(banyak_membimbing1+banyak_membimbing2)'
            .'+honor_penguji_skripsi*banyak_menguji_skripsi'
            .'+honor_penguji_proposal*banyak_menguji_proposal'
            .'+honor_penguji_seminar*banyak_menguji_seminar)';
        $persenPajakSql = "(CASE WHEN golongan=4 AND status=1 THEN 0.15 ELSE 0.05 END)";

        return (new EloquentDataTable($query))
            ->addColumn('action', function($row){
                $action = ' <a href="'.route('paymentreports.edit',$row->id).'" class="btn btn-outline-primary btn-sm action">E</a> ';
                return $action;
            })
            ->editColumn('dosen', fn ($row) => $row->dosen ?? '')
            ->editColumn('departemen_id', fn ($row) => $row->departemen_id ?? '')
            ->editColumn('status_nama', fn ($row) => $row->status_nama)
            ->editColumn('golongan_nama', fn ($row) => $row->golongan_nama)
            ->editColumn('jumlah_honor_pembimbing', fn ($row) => $row->jumlah_honor_pembimbing)
            ->editColumn('jumlah_honor_penguji_skripsi', fn ($row) => $row->jumlah_honor_penguji_skripsi)
            ->editColumn('jumlah_honor_penguji_proposal', fn ($row) => $row->jumlah_honor_penguji_proposal)
            ->editColumn('jumlah_honor_penguji_seminar', fn ($row) => $row->jumlah_honor_penguji_seminar)
            ->editColumn('total_honor', fn ($row) => $row->total_honor)
            ->editColumn('potong_pajak', fn ($row) => $row->potong_pajak)
            ->editColumn('honor_dibayar', fn ($row) => $row->honor_dibayar)
            ->filterColumn('dosen', fn ($q, $kw) => $q->whereHas('lecture', fn ($q2) => $q2->where('nama', 'like', "%{$kw}%")))
            ->orderColumn('dosen', fn ($q, $dir) => $q->orderBy(Lecture::select('nama')->whereColumn('lectures.id', 'exam_payment_reports.lecture_id'), $dir))
            ->orderColumn('status_nama', fn ($q, $dir) => $q->orderBy('status', $dir))
            ->orderColumn('golongan_nama', fn ($q, $dir) => $q->orderBy('golongan', $dir))
            ->orderColumn('jumlah_honor_pembimbing', fn ($q, $dir) => $q->orderByRaw("honor_pembimbing*(banyak_membimbing1+banyak_membimbing2) {$dir}"))
            ->orderColumn('jumlah_honor_penguji_skripsi', fn ($q, $dir) => $q->orderByRaw("honor_penguji_skripsi*banyak_menguji_skripsi {$dir}"))
            ->orderColumn('jumlah_honor_penguji_proposal', fn ($q, $dir) => $q->orderByRaw("honor_penguji_proposal*banyak_menguji_proposal {$dir}"))
            ->orderColumn('jumlah_honor_penguji_seminar', fn ($q, $dir) => $q->orderByRaw("honor_penguji_seminar*banyak_menguji_seminar {$dir}"))
            ->orderColumn('total_honor', fn ($q, $dir) => $q->orderByRaw("{$totalHonorSql} {$dir}"))
            ->orderColumn('potong_pajak', fn ($q, $dir) => $q->orderByRaw("({$persenPajakSql}*{$totalHonorSql}) {$dir}"))
            ->orderColumn('honor_dibayar', fn ($q, $dir) => $q->orderByRaw("((1-{$persenPajakSql})*{$totalHonorSql}) {$dir}"))
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ExamPaymentReport $model): QueryBuilder
    {
        return $model->with('lecture')
            ->where('report_date_id',$this->report_date_id)
            ->where('status',$this->pns)
            ->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('viewexampaymentreports-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax(url()->current())
                    //->dom('Bfrtip')
                    ->orderBy(2,3)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        // Button::make('csv'),
                        // Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
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
                  ->width(60)
                  ->addClass('text-center'),
            // Column::make('id'),
            Column::make('tanggal_laporan'),
            Column::make('departemen_id'),
            Column::make('dosen'),
            Column::make('status_nama')->title('status'),
            Column::make('golongan_nama')->title('gol'),
            Column::make('npwp'),
            Column::make('rekening'),
            Column::make('jabatan_akademik'),
            Column::make('pendidikan'),
            Column::make('honor_pembimbing'),
            Column::make('honor_penguji_skripsi'),
            Column::make('honor_penguji_proposal'),
            Column::make('honor_penguji_seminar'),
            Column::make('banyak_membimbing1'),
            Column::make('banyak_membimbing2'),
            Column::make('banyak_menguji_skripsi'),
            Column::make('banyak_menguji_proposal'),
            Column::make('banyak_menguji_seminar'),
            Column::make('jumlah_honor_pembimbing'),
            Column::make('jumlah_honor_penguji_skripsi'),
            Column::make('jumlah_honor_penguji_proposal'),
            Column::make('jumlah_honor_penguji_seminar'),
            Column::make('total_honor'),
            Column::make('potong_pajak')->title('PAJAK'),
            Column::make('honor_dibayar')->title('JUMLAH'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'laporan-honor-membimbing-menguji-skripsi-' . date('YmdHis');
    }
}
