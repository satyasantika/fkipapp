<?php

namespace App\Filament\Resources\LectureResource\Pages;

use App\Filament\Resources\LectureResource;
use App\Services\LectureNuptkService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLectures extends ListRecords
{
    protected static string $resource = LectureResource::class;

    protected function getHeaderActions(): array
    {
        $bolehIsiNuptk = fn (): bool => auth()->user()?->hasAnyRole(['admin', 'keuangan']) ?? false;

        return [
            Actions\Action::make('tempelNuptk')
                ->label('Tempel NUPTK')
                ->icon('heroicon-o-clipboard-document')
                ->color('gray')
                ->visible($bolehIsiNuptk)
                ->modalHeading('Tempel NUPTK')
                ->modalDescription('Salin kolom dari Excel lalu tempel di sini, satu dosen per baris: NIDN, NUPTK, Nama, Kode Jurusan. Dosen dengan NIDN yang sudah ada diperbarui NUPTK-nya; NIDN yang belum ada dibuat sebagai dosen baru bila nama & kode jurusan diisi.')
                ->modalSubmitActionLabel('Proses')
                ->form([
                    Forms\Components\Textarea::make('teks')
                        ->label('Data')
                        ->placeholder("0412345678\t1234567890123456\tNama Dosen\t3")
                        ->rows(12)
                        ->required(),
                ])
                ->action(function (array $data, LectureNuptkService $service) {
                    $rows = $service->parse($data['teks']);

                    if (! $rows) {
                        Notification::make()->title('Tidak ada baris yang bisa dibaca')->warning()->send();

                        return;
                    }

                    $this->notifyHasil('Tempel NUPTK selesai', $service->apply($rows));
                }),
            Actions\Action::make('isiNuptkSintesys')
                ->label('Isi NUPTK dari Sintesys')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible($bolehIsiNuptk)
                ->modalDescription('NUPTK yang masih kosong diisi dari data penguji ujian Sintesys pada rentang tanggal ini (dicocokkan lewat NIDN). Tidak membuat dosen baru dan tidak menimpa NUPTK yang sudah terisi.')
                ->modalSubmitActionLabel('Isi NUPTK')
                ->form([
                    Forms\Components\DatePicker::make('tanggal_mulai')
                        ->default(now()->subYear()->toDateString())
                        ->required(),
                    Forms\Components\DatePicker::make('tanggal_selesai')
                        ->default(now()->toDateString())
                        ->required(),
                ])
                ->action(function (array $data, LectureNuptkService $service) {
                    $hasil = $service->fillFromSintesys($data['tanggal_mulai'], $data['tanggal_selesai']);

                    $this->notifyHasil('Isi NUPTK dari Sintesys selesai', $hasil);
                }),
            Actions\CreateAction::make()
                ->label('Dosen')
                ->icon('heroicon-o-plus'),
        ];
    }

    private function notifyHasil(string $title, array $hasil): void
    {
        $body = "Diperbarui: {$hasil['diperbarui']}, dibuat: {$hasil['dibuat']}, tidak berubah: {$hasil['tidak_berubah']}, dilewati: ".count($hasil['dilewati']).'.';

        if (! empty($hasil['gagal_jurusan'])) {
            $body .= ' Gagal ditarik dari: '.implode(', ', $hasil['gagal_jurusan']).'.';
        }

        if ($hasil['dilewati']) {
            $sisa = count($hasil['dilewati']) - 10;
            $body .= '<br>'.collect($hasil['dilewati'])->take(10)->map(fn ($alasan) => e($alasan))->implode('<br>')
                .($sisa > 0 ? "<br>dan {$sisa} lainnya." : '');
        }

        Notification::make()
            ->title($title)
            ->body(str($body)->toHtmlString())
            ->color($hasil['dilewati'] ? 'warning' : 'success')
            ->icon($hasil['dilewati'] ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
            ->persistent()
            ->send();
    }
}
