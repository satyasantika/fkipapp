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

class ViewExamNotReportedDataTable extends DataTable
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
                $action = ' <form id="set-form" action='.route('reportdates.setreportdate',$row->id).' method="POST">';
                $action = $action.'<input type="hidden" name="_token" value='.csrf_token().'>';
                $action = $action.'<input type="hidden" name="_method" value="PUT">';
                $action = $action.'<input type="hidden" name="report_date_id" value='.$this->report_date_id.'>';
                $action = $action.'<button type="submit" class="btn btn-success btn-sm">UP</button> </form> ';
                return $action;
            })
            ->editColumn('dilaporkan',function($row){
                return $row->dilaporkan ? 'sudah' : 'belum' ;
            })
            ->editColumn('pembimbing1_nama',function($row){
                return is_null($row->pembimbing1_nama) ? '' : $row->pembimbing1_nama ;
            })
            ->editColumn('pembimbing2_nama',function($row){
                return is_null($row->pembimbing2_nama) ? '' : $row->pembimbing2_nama ;
            })
            ->editColumn('penguji1_nama',function($row){
                return is_null($row->penguji1_nama) ? '' : $row->penguji1_nama ;
            })
            ->editColumn('penguji2_nama',function($row){
                return is_null($row->penguji2_nama) ? '' : $row->penguji2_nama ;
            })
            ->editColumn('penguji3_nama',function($row){
                return is_null($row->penguji3_nama) ? '' : $row->penguji3_nama ;
            })
            ->editColumn('created_at', function($row) {
                return $row->updated_at->format('Y-m-d');
            })
            ->editColumn('updated_at', function($row) {
                return $row->updated_at->format('Y-m-d');
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ViewExamRegistration $model): QueryBuilder
    {
        if (auth()->user()->hasRole('keuangan')) {
            return $model->whereNull('report_date_id')
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
                    ->setTableId('viewexamnotreported-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
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
            Column::make('dilaporkan')->title('lapor?'),
            Column::make('ujian'),
            Column::make('tanggal_ujian'),
            Column::make('nim'),
            Column::make('mahasiswa'),
            Column::make('pembimbing1_nama')->title('Pemb.1'),
            Column::make('pembimbing2_nama')->title('Pemb.2'),
            Column::make('penguji1_nama')->title('Peng.1'),
            Column::make('penguji2_nama')->title('Peng.2'),
            Column::make('penguji3_nama')->title('Peng.3'),
            Column::make('created_at'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ViewExamNotReported_' . date('YmdHis');
    }
}
