<?php

namespace App\DataTables;

use App\Models\ViewExamDate;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ViewExamDateDataTable extends DataTable
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
                $action = ' <a href="'.route('reports.by.date',$row->tanggal_ujian).'" class="btn btn-outline-primary btn-sm action">detail</a> ';
                return $action;
            })
            ->editColumn('tanggal_ujian',function($row){
                return \Carbon\Carbon::parse($row->tanggal_ujian)->locale('id')->translatedFormat('l, d F Y');
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ViewExamDate $model): QueryBuilder
    {
        if (auth()->user()->hasRole('jurusan')) {
            return $model->where('departement_id',auth()->user()->departement_id)
                        ->newQuery();
        } else {
            return $model->newQuery();
        }
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('viewexamdates-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
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
            Column::make('tanggal_ujian'),
            Column::make('total'),
            Column::make('sempro'),
            Column::make('semhas'),
            Column::make('sidang'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ViewExamDates_' . date('YmdHis');
    }
}
