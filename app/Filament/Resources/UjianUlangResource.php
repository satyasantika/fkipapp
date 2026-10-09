<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UjianUlangResource\Pages;
use App\Models\ExamRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Daftar read-only ujian ulang (lihat ExamRegistration::scopeUjianUlang()):
 * ujian jenis sama yang sudah digantikan ujian lebih baru atau yang sudah
 * dilaporkan. Baris di sini disembunyikan dari daftar/hitungan "belum
 * dilaporkan" dan tidak bisa dilaporkan untuk honor.
 */
class UjianUlangResource extends Resource
{
    protected static ?string $model = ExamRegistration::class;

    protected static ?string $slug = 'ujian-ulang';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Ujian Ulang';

    protected static ?string $modelLabel = 'Ujian Ulang';

    protected static ?string $pluralModelLabel = 'Ujian Ulang';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'keuangan']) ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->ujianUlang()
            ->with(['exam_type', 'student.departement']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exam_type.singkat_ujian')
                    ->label('Ujian')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'semhas' => 'info',
                        'sidang' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('tanggal_ujian')
                    ->label('Tanggal Ujian')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.nama')
                    ->label('Mahasiswa')
                    ->description(fn (ExamRegistration $record): ?string => $record->student
                        ? $record->student->nim.' · '.($record->student->departement?->nama ?? '-')
                        : null)
                    ->searchable(['nama', 'nim']),
                Tables\Columns\TextColumn::make('ujian_ke')
                    ->label('Ujian Ke-'),
                Tables\Columns\TextColumn::make('alasan')
                    ->label('Digantikan oleh')
                    ->getStateUsing(function (ExamRegistration $record): string {
                        $pengganti = $record->ujianPengganti();

                        if (! $pengganti) {
                            return '-';
                        }

                        return $pengganti->tanggal_ujian?->format('d M Y').' - '
                            .($pengganti->dilaporkan ? 'sudah dilaporkan' : 'ujian terbaru');
                    }),
            ])
            ->defaultSort('tanggal_ujian', 'desc')
            ->paginationPageOptions([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUjianUlang::route('/'),
        ];
    }
}
