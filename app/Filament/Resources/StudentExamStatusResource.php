<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentExamStatusResource\Pages;
use App\Models\Student;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Rekap read-only per mahasiswa (bukan per registrasi seperti
 * ExamRegistrationResource) - satu baris per mahasiswa, kolom tanggal
 * sempro/semhas/sidang ditandai sudah/belum dilaporkan, supaya jurusan/
 * keuangan bisa cepat melihat ujian mana yang telat dilaporkan atau
 * telat disinkronkan dari Sintesys.
 */
class StudentExamStatusResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?string $navigationLabel = 'Status Ujian Mahasiswa';

    protected static ?string $modelLabel = 'Status Ujian Mahasiswa';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        // Murni monitoring, tidak ada alur tambah/edit di sini - data
        // mahasiswa/registrasi ujian dikelola lewat resource lain
        // (StudentResource/ExamRegistrationResource).
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['latestSempro', 'latestSemhas', 'latestSidang']);

        if (auth()->user()?->hasRole('jurusan')) {
            $query->where('departement_id', auth()->user()->departement_id);
        }

        return $query;
    }

    private static function tanggalColumn(string $relation, string $label): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make("{$relation}.tanggal_ujian")
            ->label($label)
            ->badge()
            ->formatStateUsing(fn (?string $state, Student $record): string => $record->{$relation}
                ? Carbon::parse($state)->format('d M Y').' - '.($record->{$relation}->dilaporkan ? 'Sudah' : 'Belum')
                : '-')
            ->color(fn (Student $record): string => $record->{$relation}
                ? ($record->{$relation}->dilaporkan ? 'success' : 'gray')
                : 'gray');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),
                static::tanggalColumn('latestSempro', 'Tanggal Sempro'),
                static::tanggalColumn('latestSemhas', 'Tanggal Semhas'),
                static::tanggalColumn('latestSidang', 'Tanggal Sidang'),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentExamStatuses::route('/'),
        ];
    }
}
