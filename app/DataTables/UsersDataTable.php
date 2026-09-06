<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('user', function ($row) {
                return '<div class="fw-semibold">'.e($row->name).'</div>'
                    .'<div class="text-muted small">@'.e($row->username).'</div>';
            })
            ->filterColumn('user', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('username', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('user', 'name $1')
            ->addColumn('action', function($row){
                $action = ' <a href="'.route('users.edit',$row->id).'" class="btn btn-outline-primary btn-sm action" title="Ubah"><i class="bi bi-pencil-square"></i></a> ';
                if ($row->id !== auth()->id() && ! $row->hasRole('admin')) {
                    $action .= '<form action="'.route('impersonate.take',$row->id).'" method="POST" class="d-inline">'
                        .csrf_field()
                        .'<button type="submit" class="btn btn-outline-secondary btn-sm action" title="Impersonate" onclick="return confirm(\'Login sebagai '.e($row->name).'?\');"><i class="bi bi-incognito"></i></button>'
                        .'</form>';
                }
                return $action;
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('users-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax(url()->current())
                    //->dom('Bfrtip')
                    ->orderBy(2,'ascending')
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
                    ->width(60)
                    ->addClass('text-center'),
            Column::computed('user')
                    ->title('Pengguna')
                    ->searchable(true)
                    ->orderable(true),
            Column::make('departement_id')->title('jurusan'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
