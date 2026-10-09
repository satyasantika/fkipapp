<?php

namespace App\Filament\Resources\StudentExamStatusResource\Pages;

use App\Filament\Resources\StudentExamStatusResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Database\Eloquent\Builder;

class ListStudentExamStatuses extends ListRecords
{
    protected static string $resource = StudentExamStatusResource::class;

    public bool $belumDilaporkanOnly = false;

    private bool $filterHookRegistered = false;

    /**
     * Filter "Belum Dilaporkan" dibuat manual (properti + tombol lewat
     * render hook), bukan Table::filters() bawaan, supaya tombolnya bisa
     * ditaruh persis di sebelah pencarian - pola identik
     * ListExamRegistrations::registerDilaporkanFilterHook().
     */
    public function table(Table $table): Table
    {
        $this->registerFilterHook();

        return parent::table($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $this->belumDilaporkanOnly
                ? $query->where(function (Builder $q) {
                    $q->whereHas('latestSempro', fn (Builder $r) => $r->where('dilaporkan', false))
                        ->orWhereHas('latestSemhas', fn (Builder $r) => $r->where('dilaporkan', false))
                        ->orWhereHas('latestSidang', fn (Builder $r) => $r->where('dilaporkan', false));
                })
                : $query);
    }

    protected function registerFilterHook(): void
    {
        if ($this->filterHookRegistered) {
            return;
        }

        $this->filterHookRegistered = true;

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_SEARCH_BEFORE,
            fn (): string => view('filament.resources.student-exam-status-resource.belum-dilaporkan-filter', [
                'active' => $this->belumDilaporkanOnly,
            ])->render(),
            scopes: static::class,
        );
    }

    public function toggleBelumDilaporkan(): void
    {
        $this->belumDilaporkanOnly = ! $this->belumDilaporkanOnly;
        $this->resetTable();
    }
}
