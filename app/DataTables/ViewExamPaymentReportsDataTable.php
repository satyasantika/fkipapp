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
            ->addColumn('action', 'viewexampaymentreports.action')
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ViewExamPaymentReport $model): QueryBuilder
    {
        return $model->newQuery();
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
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
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
            // Column::computed('action')
            //       ->exportable(false)
            //       ->printable(false)
            //       ->width(60)
            //       ->addClass('text-center'),
            // Column::make('id'),
            Column::make('kode_laporan'),
            Column::make('dosen'),
            Column::make('status'),
            Column::make('golongan'),
            Column::make('npwp'),
            Column::make('rekening'),
            Column::make('jabatan_akademik'),
            Column::make('pendidikan'),
            Column::make('honor_pembimbing'),
            Column::make('honor_penguji_skripsi'),
            Column::make('honor_penguji_proposal'),
            Column::make('honor_penguji_seinar'),
            Column::make('banyak_membimbing1'),
            Column::make('banyak_membimbing2'),
            Column::make('banyak_menguji_skripsi'),
            Column::make('banyak_menguji_proposal'),
            Column::make('banyak_menguji_seminar'),
            Column::make('created_at'),
            Column::make('updated_at'),
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
