<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamTypeResource\Pages;
use App\Filament\Resources\ExamTypeResource\RelationManagers;
use App\Models\ExamType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamTypeResource extends Resource
{
    protected static ?string $model = ExamType::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'create' => Pages\CreateExamType::route('/create'),
            'edit' => Pages\EditExamType::route('/{record}/edit'),
        ];
    }
}
