<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartementResource\Pages;
use App\Filament\Resources\DepartementResource\RelationManagers;
use App\Filament\Concerns\GuardsBulkDeletion;
use App\Filament\Concerns\ShowsDeletionBlockAlert;
use App\Models\Departement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class DepartementResource extends Resource
{
    use GuardsBulkDeletion, ShowsDeletionBlockAlert;

    protected static ?string $model = Departement::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Jurusan';

    protected static ?string $modelLabel = 'Jurusan';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::deletionBlockAlertField(),
                Forms\Components\TextInput::make('nama')
                    ->maxLength(255),
                Forms\Components\TextInput::make('mapel')
                    ->maxLength(255),
                Forms\Components\TextInput::make('singkatan')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mapel')
                    ->searchable(),
                Tables\Columns\TextColumn::make('singkatan')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus jurusan ini?')
                    ->modalDescription('Data jurusan ini akan dihapus permanen.')
                    ->before(function (Departement $record) {
                        if ($reason = $record->deletionBlockReason()) {
                            Notification::make()->title($reason)->danger()->send();

                            throw new Halt();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Hapus jurusan yang dipilih?')
                        ->modalDescription('Semua jurusan yang dipilih akan dihapus permanen.')
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
            'index' => Pages\ListDepartements::route('/'),
        ];
    }
}
