<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamPaymentResource\Pages;
use App\Filament\Resources\ExamPaymentResource\RelationManagers;
use App\Models\ExamPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamPaymentResource extends Resource
{
    protected static ?string $model = ExamPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Rate Honor';

    protected static ?string $modelLabel = 'Rate Honor';

    // Data rate honor cuma urusan bagian keuangan - dulu cuma dicek admin,
    // sekarang gerbangnya keuangan, dan dipasang di canViewAny/canCreate/
    // canEdit/canDelete sekaligus (bukan cuma shouldRegisterNavigation),
    // supaya role lain juga tidak bisa nyelonong lewat URL langsung
    // /admin/exam-payments/{id}/edit.
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('keuangan') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('keuangan') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('keuangan') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasRole('keuangan') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('keuangan') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->maxLength(255),
                Forms\Components\TextInput::make('jabatan_akademik')
                    ->maxLength(255),
                Forms\Components\TextInput::make('pendidikan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('honor')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(16777215),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan_akademik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pendidikan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('honor')
                    ->numeric(),
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
                        ->modalHeading('Hapus rate honor yang dipilih?')
                        ->modalDescription('Semua rate honor yang dipilih akan dihapus permanen.'),
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
            'index' => Pages\ListExamPayments::route('/'),
            'create' => Pages\CreateExamPayment::route('/create'),
            'edit' => Pages\EditExamPayment::route('/{record}/edit'),
        ];
    }
}
