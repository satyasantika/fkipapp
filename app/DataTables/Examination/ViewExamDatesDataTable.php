<?php

namespace App\DataTables\Examination;

use App\Models\ViewExamDate;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ViewExamDatesDataTable extends DataTable
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
                $action = ' ';
                $action .= ' <a href="'.route('examdates.edit',$row->id).'" class="btn btn-outline-primary btn-sm action">E</a>';
                return $action;
            })
            ->editColumn('tanggal_ujian', function(ViewExamDate $row) {
                // return Carbon::createFromFormat('Y-m-d', $row->tanggal_ujian)->toDateTimeString();
                return date('d M Y',strtotime($row->tanggal_ujian));
                // return $row->tanggal_ujian->isoFormat('D MMMM Y');
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ViewExamDate $model): QueryBuilder
    {
        return $model->newQuery()->where('departement_id',auth()->user()->departement_id);
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
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('add'),
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
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
            Column::make('departement_id')->title('Kode'),
            Column::make('tanggal_ujian'),
            Column::make('kelompok_ujian'),
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
