<?php

namespace App\DataTables;

use App\Models\ViewExamPaymentReport;
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
        return (new EloquentDataTable($query))
            ->addColumn('action', function($row){
                $action = ' <a href="'.route('paymentreports.edit',$row->id).'" class="btn btn-outline-primary btn-sm action">E</a> ';
                return $action;
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ViewExamPaymentReport $model): QueryBuilder
    {
        return $model->where('report_date_id',$this->report_date_id)->where('status',$this->pns)->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('viewexampaymentreports-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
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
        return 'ViewExamPaymentReports_' . date('YmdHis');
    }
}
