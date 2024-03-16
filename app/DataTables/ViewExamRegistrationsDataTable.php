<?php

namespace App\DataTables;

use App\Models\ViewExamRegistration;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ViewExamRegistrationsDataTable extends DataTable
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
                $action = ' <a href="'.route('registrations.edit',$row->id).'" class="btn btn-outline-primary btn-sm action">E</a> ';
                return $action;
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ViewExamRegistration $model): QueryBuilder
    {
        if (auth()->user()->hasRole('jurusan')) {
            return $model->where('student_id',$this->student_id)
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
                    ->setTableId('viewexamregistrations-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(2)
                    ->selectStyleSingle()
                    ->buttons([
                        auth()->user()->hasRole('jurusan') ? Button::make('add') :'',
                        Button::make('excel'),
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
            Column::make('tanggal_ujian'),
            Column::make('ujian'),
            Column::make('mahasiswa'),
            Column::make('nim'),
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
        return 'ViewExamRegistrations_' . date('YmdHis');
    }
}
