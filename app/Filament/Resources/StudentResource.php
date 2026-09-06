<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Filament\Concerns\GuardsBulkDeletion;
use App\Models\Lecture;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class StudentResource extends Resource
{
    use GuardsBulkDeletion;

    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Mahasiswa';

    protected static ?string $modelLabel = 'Mahasiswa';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'jurusan']) ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'jurusan']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->hasRole('jurusan')) {
            $query->where('departement_id', auth()->user()->departement_id);
        }

        return $query;
    }

    private static function lectureOptionsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Lecture::query()->orderBy('nama');

        if (auth()->user()?->hasRole('jurusan')) {
            $query->where('departement_id', auth()->user()->departement_id);
        }

        return $query;
    }

    private static function lectureOptionLabel(Lecture $lecture): string
    {
        return "{$lecture->nama} - {$lecture->departement_id}";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nim')
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama')
                    ->maxLength(255),
                Forms\Components\Select::make('departement_id')
                    ->label('Jurusan')
                    ->relationship('departement', 'nama')
                    ->searchable()
                    ->preload()
                    ->disabled(fn (?Student $record): bool => $record && $record->departement_id == auth()->user()->departement_id),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),

                Forms\Components\Select::make('pembimbing1_id')
                    ->label('Pembimbing 1')
                    ->relationship('pembimbing1', 'nama', modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => self::lectureOptionsQuery())
                    ->getOptionLabelFromRecordUsing(fn (Lecture $record) => self::lectureOptionLabel($record))
                    ->searchable()
                    ->visible(fn (): bool => auth()->user()?->hasRole('jurusan') ?? false),
                Forms\Components\Select::make('pembimbing2_id')
                    ->label('Pembimbing 2')
                    ->relationship('pembimbing2', 'nama', modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => self::lectureOptionsQuery())
                    ->getOptionLabelFromRecordUsing(fn (Lecture $record) => self::lectureOptionLabel($record))
                    ->searchable()
                    ->visible(fn (): bool => auth()->user()?->hasRole('jurusan') ?? false),
                Forms\Components\Select::make('penguji1_id')
                    ->label('Penguji 1')
                    ->relationship('penguji1', 'nama', modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => self::lectureOptionsQuery())
                    ->getOptionLabelFromRecordUsing(fn (Lecture $record) => self::lectureOptionLabel($record))
                    ->searchable()
                    ->visible(fn (): bool => auth()->user()?->hasRole('jurusan') ?? false),
                Forms\Components\Select::make('penguji2_id')
                    ->label('Penguji 2')
                    ->relationship('penguji2', 'nama', modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => self::lectureOptionsQuery())
                    ->getOptionLabelFromRecordUsing(fn (Lecture $record) => self::lectureOptionLabel($record))
                    ->searchable()
                    ->visible(fn (): bool => auth()->user()?->hasRole('jurusan') ?? false),
                Forms\Components\Select::make('penguji3_id')
                    ->label('Penguji 3')
                    ->relationship('penguji3', 'nama', modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => self::lectureOptionsQuery())
                    ->getOptionLabelFromRecordUsing(fn (Lecture $record) => self::lectureOptionLabel($record))
                    ->searchable()
                    ->visible(fn (): bool => auth()->user()?->hasRole('jurusan') ?? false),
                Forms\Components\Select::make('ketuapenguji_id')
                    ->label('Ketua Penguji')
                    ->options(fn (): array => self::lectureOptionsQuery()->get()->mapWithKeys(
                        fn (Lecture $lecture) => [$lecture->id => self::lectureOptionLabel($lecture)]
                    )->all())
                    ->searchable()
                    ->visible(fn (): bool => auth()->user()?->hasRole('jurusan') ?? false),
                Forms\Components\DatePicker::make('tanggal_proposal')
                    ->visible(fn (): bool => auth()->user()?->hasRole('jurusan') ?? false),
                Forms\Components\DatePicker::make('tanggal_seminar')
                    ->visible(fn (): bool => auth()->user()?->hasRole('jurusan') ?? false),
                Forms\Components\DatePicker::make('tanggal_skripsi')
                    ->visible(fn (): bool => auth()->user()?->hasRole('jurusan') ?? false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('departement_id')
                    ->label('Kode'),
                Tables\Columns\TextColumn::make('nim')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pembimbing1.nama')
                    ->label('Pembimbing 1')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('pembimbing2.nama')
                    ->label('Pembimbing 2')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('penguji1.nama')
                    ->label('Penguji 1')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('penguji2.nama')
                    ->label('Penguji 2')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('penguji3.nama')
                    ->label('Penguji 3')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_proposal')
                    ->date(),
                Tables\Columns\TextColumn::make('tanggal_seminar')
                    ->date(),
                Tables\Columns\TextColumn::make('tanggal_skripsi')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('ujian')
                    ->label('Ujian')
                    ->icon('heroicon-o-academic-cap')
                    ->url(fn (Student $record): string => route('registrations.show.student', $record->id))
                    ->visible(fn (): bool => auth()->user()?->hasRole('jurusan') ?? false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Hapus data mahasiswa yang dipilih?')
                        ->modalDescription('Semua data mahasiswa yang dipilih akan dihapus permanen.')
                        ->before(fn (Collection $records) => static::haltIfAnyRecordIsReferenced($records)),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
