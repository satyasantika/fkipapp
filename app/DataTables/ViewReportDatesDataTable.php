<?php

namespace App\DataTables;

use App\Models\ReportDate;
use Laraindo\RupiahFormat;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ViewReportDatesDataTable extends DataTable
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
                $action = ' <a href="'.route('reportdates.edit',$row->id).'" class="btn btn-outline-primary btn-sm action">E</a> ';
                $action = $action.' <a href="'.route('reportdates.reportedlist',$row->id).'" class="btn btn-outline-success btn-sm action">L</a> ';
                $action = $action.' <a href="'.route('reports.fresh.periode',$row->id).'" class="btn btn-outline-dark btn-sm action">R</a> ';
                return $action;
            })
            ->editColumn('dibayar',function($row){
                if (!is_null($row->dibayar)) {
                    $action = RupiahFormat::currency($row->dibayar);
                    $action = $action.' ('.RupiahFormat::terbilang($row->dibayar).')';
                } else {
                    $action = NULL;
                }
                return $action;
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ReportDate $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('viewreportdates-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('add'),
                        // Button::make('excel'),
                        // Button::make('csv'),
                        // Button::make('pdf'),
                        // Button::make('print'),
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
                  ->width(100)
                  ->addClass('text-center'),
            // Column::make('id'),
            Column::make('tanggal')->width(100),
            Column::make('deskripsi'),
            Column::make('dibayar')
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ViewReportDates_' . date('YmdHis');
    }
}
