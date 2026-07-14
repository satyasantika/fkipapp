<?php

namespace App\DataTables;

use App\Models\Lecture;
use App\Models\Student;
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
                return $row->pembimbing1?->nama ?? '';
            })
            ->editColumn('pembimbing_2',function($row){
                return $row->pembimbing2?->nama ?? '';
            })
            ->editColumn('penguji_1',function($row){
                return $row->penguji1?->nama ?? '';
            })
            ->editColumn('penguji_2',function($row){
                return $row->penguji2?->nama ?? '';
            })
            ->editColumn('penguji_3',function($row){
                return $row->penguji3?->nama ?? '';
            })
            ->filterColumn('pembimbing_1', fn ($q, $kw) => $q->whereHas('pembimbing1', fn ($q2) => $q2->where('nama', 'like', "%{$kw}%")))
            ->orderColumn('pembimbing_1', fn ($q, $dir) => $q->orderBy(Lecture::select('nama')->whereColumn('lectures.id', 'students.pembimbing1_id'), $dir))
            ->filterColumn('pembimbing_2', fn ($q, $kw) => $q->whereHas('pembimbing2', fn ($q2) => $q2->where('nama', 'like', "%{$kw}%")))
            ->orderColumn('pembimbing_2', fn ($q, $dir) => $q->orderBy(Lecture::select('nama')->whereColumn('lectures.id', 'students.pembimbing2_id'), $dir))
            ->filterColumn('penguji_1', fn ($q, $kw) => $q->whereHas('penguji1', fn ($q2) => $q2->where('nama', 'like', "%{$kw}%")))
            ->orderColumn('penguji_1', fn ($q, $dir) => $q->orderBy(Lecture::select('nama')->whereColumn('lectures.id', 'students.penguji1_id'), $dir))
            ->filterColumn('penguji_2', fn ($q, $kw) => $q->whereHas('penguji2', fn ($q2) => $q2->where('nama', 'like', "%{$kw}%")))
            ->orderColumn('penguji_2', fn ($q, $dir) => $q->orderBy(Lecture::select('nama')->whereColumn('lectures.id', 'students.penguji2_id'), $dir))
            ->filterColumn('penguji_3', fn ($q, $kw) => $q->whereHas('penguji3', fn ($q2) => $q2->where('nama', 'like', "%{$kw}%")))
            ->orderColumn('penguji_3', fn ($q, $dir) => $q->orderBy(Lecture::select('nama')->whereColumn('lectures.id', 'students.penguji3_id'), $dir))
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Student $model): QueryBuilder
    {
        $query = $model->with(['pembimbing1', 'pembimbing2', 'penguji1', 'penguji2', 'penguji3']);

        if (auth()->user()->hasRole('jurusan')) {
            return $query->where('departement_id',auth()->user()->departement_id)->newQuery();
        } else {
            return $query->newQuery();
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
