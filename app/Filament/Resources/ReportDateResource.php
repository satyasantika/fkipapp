<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportDateResource\Pages;
use App\Filament\Resources\ReportDateResource\RelationManagers;
use App\Filament\Concerns\ShowsDeletionBlockAlert;
use App\Models\ExamRegistration;
use App\Models\ReportDate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReportDateResource extends Resource
{
    use ShowsDeletionBlockAlert;

    protected static ?string $model = ReportDate::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Penarikan Laporan';

    protected static ?string $modelLabel = 'Penarikan Laporan';

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::deletionBlockAlertField(),
                Forms\Components\DatePicker::make('tanggal')
                    ->required(),
                Forms\Components\Textarea::make('deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('dibayar')
                    ->label('Dibayar')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date(),
                Tables\Columns\TextColumn::make('deskripsi'),
                Tables\Columns\TextColumn::make('dibayar')
                    ->money('idr', divideBy: 1),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus tanggal penarikan laporan ini?')
                    ->modalDescription('Tanggal penarikan laporan ini akan dihapus permanen.')
                    ->before(function (ReportDate $record) {
                        if ($reason = $record->deletionBlockReason()) {
                            Notification::make()->title($reason)->danger()->send();

                            throw new Halt();
                        }
                    }),
                Tables\Actions\Action::make('reportedList')
                    ->label('L')
                    ->tooltip('Daftar yang sudah dilaporkan')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->url(fn (ReportDate $record): string => static::getUrl('reported-list', ['record' => $record])),
                Tables\Actions\Action::make('freshByPeriode')
                    ->label('R')
                    ->tooltip('Rekap ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->url(fn (ReportDate $record): string => route('reports.fresh.periode', $record->id)),
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListReportDates::route('/'),
            'not-reported-list' => Pages\NotReportedList::route('/{record}/not-reported'),
            'reported-list' => Pages\ReportedList::route('/{record}/reported'),
            'sidang-confirmed-list' => Pages\SidangConfirmedList::route('/{record}/sidang-confirmed'),
        ];
    }
}
