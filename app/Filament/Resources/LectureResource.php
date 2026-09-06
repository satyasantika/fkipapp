<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LectureResource\Pages;
use App\Filament\Resources\LectureResource\RelationManagers;
use App\Filament\Concerns\GuardsBulkDeletion;
use App\Filament\Concerns\ShowsDeletionBlockAlert;
use App\Models\Lecture;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class LectureResource extends Resource
{
    use GuardsBulkDeletion, ShowsDeletionBlockAlert;

    protected static ?string $model = Lecture::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Dosen';

    protected static ?string $modelLabel = 'Dosen';

    private const JABATAN_AKADEMIKS = ['Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar'];

    private const GOLONGANS = ['3b', '3c', '3d', '4a', '4b', '4c', '4d', '4e'];

    private const PENDIDIKANS = ['S2', 'S3'];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'jurusan', 'keuangan']) ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'jurusan', 'keuangan']) ?? false;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::deletionBlockAlertField(),
                Forms\Components\Select::make('departement_id')
                    ->label('Jurusan')
                    ->relationship('departement', 'nama')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('gelar_depan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama')
                    ->maxLength(255),
                Forms\Components\TextInput::make('gelar_belakang')
                    ->maxLength(255),
                Forms\Components\Checkbox::make('pns')
                    ->label('ASN'),
                Forms\Components\TextInput::make('nidn')
                    ->maxLength(255),
                Forms\Components\TextInput::make('nip')
                    ->maxLength(255),
                Forms\Components\Select::make('jabatan_akademik')
                    ->options(array_combine(self::JABATAN_AKADEMIKS, self::JABATAN_AKADEMIKS)),
                Forms\Components\Select::make('golongan')
                    ->options(array_combine(self::GOLONGANS, self::GOLONGANS)),
                Forms\Components\Select::make('pendidikan')
                    ->options(array_combine(self::PENDIDIKANS, self::PENDIDIKANS)),
                Forms\Components\TextInput::make('tempat_lahir')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('tanggal_lahir'),
                Forms\Components\TextInput::make('rekening')
                    ->maxLength(255),
                Forms\Components\TextInput::make('npwp')
                    ->maxLength(255),
                Forms\Components\TextInput::make('nik')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Textarea::make('alamat')
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departement_id')
                    ->label('Kode'),
                Tables\Columns\IconColumn::make('pns')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->tooltip(fn (Lecture $record): string => $record->pns ? 'ASN' : 'Non ASN'),
                Tables\Columns\TextColumn::make('golongan'),
                Tables\Columns\TextColumn::make('jabatan_akademik')
                    ->label('JF'),
                Tables\Columns\TextColumn::make('pendidikan'),
                Tables\Columns\TextColumn::make('npwp'),
                Tables\Columns\TextColumn::make('rekening'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Hapus dosen yang dipilih?')
                        ->modalDescription('Semua dosen yang dipilih akan dihapus permanen.')
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
            'index' => Pages\ListLectures::route('/'),
            'create' => Pages\CreateLecture::route('/create'),
            'edit' => Pages\EditLecture::route('/{record}/edit'),
        ];
    }
}
