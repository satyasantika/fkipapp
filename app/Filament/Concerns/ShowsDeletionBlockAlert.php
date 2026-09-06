<?php

namespace App\Filament\Concerns;

use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/**
 * Field alert di atas form edit yang menjelaskan kenapa record ini tidak
 * bisa dihapus (lihat App\Models\Concerns\GuardsDeletionWhenReferenced) -
 * proaktif, bukan cuma notifikasi reaktif saat tombol hapus diklik.
 */
trait ShowsDeletionBlockAlert
{
    protected static function deletionBlockAlertField(): Placeholder
    {
        return Placeholder::make('deletionBlockReason')
            ->hiddenLabel()
            ->content(fn (?Model $record): HtmlString => new HtmlString(
                view('filament.deletion-block-alert', ['reason' => $record?->deletionBlockReason()])->render()
            ))
            ->visible(fn (?Model $record): bool => filled($record?->deletionBlockReason()))
            ->columnSpanFull();
    }
}
