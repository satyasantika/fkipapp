<?php

namespace App\DataTables;

use App\Models\ViewLecture;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ViewLecturesDataTable extends DataTable
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
                $action = ' <a href="'.route('lectures.edit',$row->id).'" class="btn btn-outline-primary btn-sm action">E</a> ';
                return $action;
            })
            ->editColumn('pns', function($row){
                return $row->pns ? 'ASN' : 'Non ASN';
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ViewLecture $model): QueryBuilder
    {
        if (auth()->user()->hasRole('jurusan')) {
            return $model->where('departement_id',auth()->user()->departement_id)->newQuery();
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
                    ->setTableId('viewlectures-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1,'asc')
                    ->selectStyleSingle()
                    ->buttons([
                        auth()->user()->hasRole('admin') ? Button::make('add') :'',
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
                    ->width(60)
                    ->addClass('text-center'),
            Column::make('nama'),
            Column::make('departement_id')->title('Kode'),
            Column::make('pns')->title('status'),
            Column::make('golongan'),
            Column::make('jabatan_akademik')->title('JF'),
            Column::make('pendidikan'),
            Column::make('npwp'),
            Column::make('rekening'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ViewLectures_' . date('YmdHis');
    }
}
