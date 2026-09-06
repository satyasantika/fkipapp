<?php

namespace App\Filament\Concerns;

use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Collection;

/**
 * Dipakai lewat ->before() pada DeleteBulkAction. Kalau ada satu saja record
 * yang dipilih masih dipakai di tabel lain, seluruh batch dibatalkan (bukan
 * hapus sebagian) supaya hasilnya jelas dan mudah dijelaskan ke user - lihat
 * juga App\Models\Concerns\GuardsDeletionWhenReferenced yang jadi sumber
 * pesan "deletionBlockReason()" per model.
 */
trait GuardsBulkDeletion
{
    protected static function haltIfAnyRecordIsReferenced(Collection $records): void
    {
        $reasons = $records
            ->map(fn ($record) => $record->deletionBlockReason())
            ->filter()
            ->values();

        if ($reasons->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('Sebagian data yang dipilih tidak bisa dihapus')
            ->body($reasons->implode("\n"))
            ->danger()
            ->send();

        throw new Halt();
    }
}
