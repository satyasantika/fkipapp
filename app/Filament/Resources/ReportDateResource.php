<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportDateResource\Pages;
use App\Filament\Resources\ReportDateResource\RelationManagers;
use App\Filament\Concerns\ShowsDeletionBlockAlert;
use App\Models\ExamPaymentReport;
use App\Models\ExamRegistration;
use App\Models\ReportDate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

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
                    ->date('l, d M Y')
                    ->icon('heroicon-o-calendar-days')
                    ->weight(FontWeight::SemiBold)
                    ->description(function (ReportDate $record): \Illuminate\Support\HtmlString {
                        // HtmlString supaya baris terpisah beneran ({{ }} Blade
                        // otomatis panggil ->toHtml() untuk Htmlable) - string
                        // biasa dengan \n akan dirender jadi satu baris saja.
                        $lastPulled = $record->last_pulled_at
                            ? $record->last_pulled_at->format('d M Y, H:i')
                            : '-';

                        if (! $record->locked_at) {
                            $lockInfo = '-';
                        } else {
                            $lockInfo = $record->locked_at->format('d M Y, H:i').($record->is_locked ? ' (dikunci)' : ' (dibuka)');
                        }

                        return new \Illuminate\Support\HtmlString(
                            '<div>Terakhir ditarik: '.e($lastPulled).'</div>'
                            .'<div>Kunci/buka terakhir: '.e($lockInfo).'</div>'
                        );
                    }),
                Tables\Columns\TextColumn::make('is_locked')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Terkunci' : 'Terbuka')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('deskripsi')
                    ->placeholder('Tidak ada catatan')
                    ->color('gray')
                    ->wrap(),
                Tables\Columns\TextColumn::make('dibayar')
                    ->money('idr', divideBy: 1)
                    ->weight(FontWeight::Bold)
                    ->description(function (ReportDate $record): \Illuminate\Support\HtmlString {
                        // total_honor adalah accessor (dihitung dari kolom honor_*/banyak_*),
                        // bukan kolom asli, jadi harus dijumlah di PHP lewat Collection::sum(),
                        // tidak bisa lewat SUM() SQL - sama seperti dibayar dihitung ulang di
                        // ExamPaymentReportService::store().
                        //
                        // Dirender lewat view (bukan HtmlString manual) supaya benar-benar
                        // memakai <x-filament::badge> asli - warna non-gray Filament
                        // ditentukan lewat inline style dari get_color_css_variables(),
                        // bukan kelas Tailwind semacam bg-success-50 yang kelihatan
                        // masuk akal tapi tidak pernah dikompilasi di panel ini (tidak
                        // ada build Tailwind sendiri, lihat catatan di student-card.blade.php),
                        // jadi badge warna itu tidak bisa ditiru manual lewat HtmlString.
                        $reports = ExamPaymentReport::where('report_date_id', $record->id)->get();

                        $asn = Number::currency($reports->where('status', 1)->sum('total_honor'), in: 'IDR', locale: 'id');
                        $nonAsn = Number::currency($reports->where('status', 0)->sum('total_honor'), in: 'IDR', locale: 'id');

                        return new \Illuminate\Support\HtmlString(
                            view('filament.resources.report-date-resource.tables.dibayar-badges', [
                                'asn' => $asn,
                                'nonAsn' => $nonAsn,
                            ])->render()
                        );
                    }),
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
                Tables\Actions\Action::make('toggleLock')
                    ->label(fn (ReportDate $record): string => $record->is_locked ? 'Buka Kunci' : 'Kunci')
                    ->tooltip(fn (ReportDate $record): string => $record->is_locked ? 'Buka Kunci' : 'Kunci')
                    ->icon(fn (ReportDate $record): string => $record->is_locked ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn (ReportDate $record): string => $record->is_locked ? 'danger' : 'gray')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->modalDescription(fn (ReportDate $record): string => $record->is_locked
                        ? 'Buka kunci penarikan laporan ini? Data bisa ditambah/dikurangi lagi setelah dibuka.'
                        : 'Kunci penarikan laporan ini? Data tidak bisa ditambah/dikurangi lagi selama terkunci.')
                    ->action(function (ReportDate $record): void {
                        $record->update([
                            'is_locked' => ! $record->is_locked,
                            'locked_at' => now(),
                        ]);

                        Notification::make()
                            ->title($record->is_locked ? 'Penarikan laporan dikunci' : 'Penarikan laporan dibuka kembali')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reportedList')
                    ->label('List Ujian Dilaporkan')
                    ->tooltip('List Ujian Dilaporkan')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->iconButton()
                    ->url(fn (ReportDate $record): string => static::getUrl('reported-list', ['record' => $record])),
                Tables\Actions\Action::make('verifikasiData')
                    ->label('Verifikasi Data')
                    ->tooltip('Verifikasi Data')
                    ->icon('heroicon-o-scale')
                    ->iconButton()
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->modalHeading(fn (ReportDate $record): string => 'Verifikasi Data - '.\Illuminate\Support\Carbon::parse($record->tanggal)->format('Y-m-d'))
                    ->modalContent(fn (ReportDate $record) => view('filament.resources.report-date-resource.tables.verification-slideover', [
                        'record' => $record,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Tables\Actions\Action::make('reportSectionAsn')
                    ->label('List Bayar ASN')
                    ->tooltip('List Bayar ASN')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->iconButton()
                    ->visible(fn (ReportDate $record): bool => $record->is_locked)
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->modalHeading(fn (ReportDate $record): string => 'Bayar ASN - '.\Illuminate\Support\Carbon::parse($record->tanggal)->format('Y-m-d'))
                    ->modalContent(fn (ReportDate $record) => view('filament.resources.report-date-resource.tables.payment-section-slideover', [
                        'record' => $record,
                        'pns' => 1,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Kembali ke Penarikan Laporan'),
                Tables\Actions\Action::make('reportSectionNonAsn')
                    ->label('List Bayar Non-ASN')
                    ->tooltip('List Bayar Non-ASN')
                    ->icon('heroicon-o-banknotes')
                    ->color('gray')
                    ->iconButton()
                    ->visible(fn (ReportDate $record): bool => $record->is_locked)
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->modalHeading(fn (ReportDate $record): string => 'Bayar Non-ASN - '.\Illuminate\Support\Carbon::parse($record->tanggal)->format('Y-m-d'))
                    ->modalContent(fn (ReportDate $record) => view('filament.resources.report-date-resource.tables.payment-section-slideover', [
                        'record' => $record,
                        'pns' => 0,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Kembali ke Penarikan Laporan'),
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
