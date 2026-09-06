<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamTypeResource\Pages;
use App\Filament\Resources\ExamTypeResource\RelationManagers;
use App\Filament\Concerns\GuardsBulkDeletion;
use App\Filament\Concerns\ShowsDeletionBlockAlert;
use App\Models\ExamType;
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

class ExamTypeResource extends Resource
{
    use GuardsBulkDeletion, ShowsDeletionBlockAlert;

    protected static ?string $model = ExamType::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Jenis Ujian';

    protected static ?string $modelLabel = 'Jenis Ujian';

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
                Forms\Components\TextInput::make('nama_ujian')
                    ->maxLength(255),
                Forms\Components\TextInput::make('kode_ujian')
                    ->maxLength(255),
                Forms\Components\TextInput::make('singkat_ujian')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_ujian')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kode_ujian')
                    ->searchable(),
                Tables\Columns\TextColumn::make('singkat_ujian')
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
                    ->visible(fn (ExamType $record): bool => ! $record->deletionBlockReason())
                    ->modalHeading('Hapus jenis ujian ini?')
                    ->modalDescription('Jenis ujian ini akan dihapus permanen dari daftar.')
                    ->before(function (ExamType $record) {
                        if ($reason = $record->deletionBlockReason()) {
                            Notification::make()->title($reason)->danger()->send();

                            throw new Halt();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Hapus jenis ujian yang dipilih?')
                        ->modalDescription('Semua jenis ujian yang dipilih akan dihapus permanen dari daftar.')
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
            'index' => Pages\ListExamTypes::route('/'),
        ];
    }
}
