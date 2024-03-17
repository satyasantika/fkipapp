<?php

namespace App\DataTables;

use App\Models\ViewStudent;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ViewStudentsDataTable extends DataTable
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
                $action = ' <a href="'.route('students.edit',$row->id).'" class="btn btn-outline-primary btn-sm action">E</a> ';
                if (auth()->user()->hasRole('jurusan')) {
                    $action .= ' <a href="'.route('registrations.show.student',$row->id).'" class="btn btn-success btn-sm action">U</a> ';
                }
                return $action;
            })
            ->editColumn('pembimbing_1',function($row){
                return is_null($row->pembimbing_1) ? '' : $row->pembimbing_1 ;
            })
            ->editColumn('pembimbing_2',function($row){
                return is_null($row->pembimbing_2) ? '' : $row->pembimbing_2 ;
            })
            ->editColumn('penguji_1',function($row){
                return is_null($row->penguji_1) ? '' : $row->penguji_1 ;
            })
            ->editColumn('penguji_2',function($row){
                return is_null($row->penguji_2) ? '' : $row->penguji_2 ;
            })
            ->editColumn('penguji_3',function($row){
                return is_null($row->penguji_3) ? '' : $row->penguji_3 ;
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ViewStudent $model): QueryBuilder
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
                    ->setTableId('viewstudents-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
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
            Column::make('departement_id')->title('Kode'),
            Column::make('nim'),
            Column::make('nama'),
            Column::make('pembimbing_1'),
            Column::make('pembimbing_2'),
            Column::make('penguji_1'),
            Column::make('penguji_2'),
            Column::make('penguji_3'),
            Column::make('tanggal_proposal'),
            Column::make('tanggal_seminar'),
            Column::make('tanggal_skripsi'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ViewStudents_' . date('YmdHis');
    }
}
