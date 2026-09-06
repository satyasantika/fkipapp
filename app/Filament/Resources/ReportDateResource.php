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
                    ->autosize()
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
                    ->visible(fn (ReportDate $record): bool => ! $record->deletionBlockReason())
                    ->modalHeading('Hapus tanggal penarikan laporan ini?')
                    ->modalDescription('Tanggal penarikan laporan ini akan dihapus permanen.')
                    ->before(function (ReportDate $record) {
                        if ($reason = $record->deletionBlockReason()) {
                            Notification::make()->title($reason)->danger()->send();

                            throw new Halt();
                        }
                    }),
                Tables\Actions\Action::make('reportedList')
                    ->label('List Ujian Dilaporkan')
                    ->tooltip('List Ujian Dilaporkan')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->iconButton()
                    ->url(fn (ReportDate $record): string => static::getUrl('reported-list', ['record' => $record])),
                Tables\Actions\Action::make('reportSectionAsn')
                    ->label('List Bayar ASN')
                    ->tooltip('List Bayar ASN')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->iconButton()
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->modalHeading(fn (ReportDate $record): string => 'Bayar ASN - '.\Illuminate\Support\Carbon::parse($record->tanggal)->format('Y-m-d'))
                    ->modalContent(fn (ReportDate $record) => view('filament.resources.report-date-resource.tables.payment-section-slideover', [
                        'record' => $record,
                        'pns' => 1,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Tables\Actions\Action::make('reportSectionNonAsn')
                    ->label('List Bayar Non-ASN')
                    ->tooltip('List Bayar Non-ASN')
                    ->icon('heroicon-o-banknotes')
                    ->color('gray')
                    ->iconButton()
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->modalHeading(fn (ReportDate $record): string => 'Bayar Non-ASN - '.\Illuminate\Support\Carbon::parse($record->tanggal)->format('Y-m-d'))
                    ->modalContent(fn (ReportDate $record) => view('filament.resources.report-date-resource.tables.payment-section-slideover', [
                        'record' => $record,
                        'pns' => 0,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
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
            'reported-list' => Pages\ReportedList::route('/{record}/reported'),
        ];
    }
}
