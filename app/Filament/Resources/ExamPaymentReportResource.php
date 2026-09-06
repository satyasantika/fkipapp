<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamPaymentReportResource\Pages;
use App\Filament\Resources\ExamPaymentReportResource\RelationManagers;
use App\Models\ExamPayment;
use App\Models\ExamPaymentReport;
use App\Models\ReportDate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamPaymentReportResource extends Resource
{
    protected static ?string $model = ExamPaymentReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Laporan Honor';

    protected static ?string $modelLabel = 'Laporan Honor';

    private const JABATAN_AKADEMIKS = ['Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar'];

    private const GOLONGANS = ['3', '4'];

    private const PENDIDIKANS = ['S2', 'S3'];

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
        // Baris exam_payment_reports hanya pernah dibuat lewat _reportStore()
        // (proses "Laporkan Ujian"/mass-report/cascade-sidang), tidak pernah
        // manual - sama seperti di aplikasi lama (route create/show kosong).
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('dosen')
                    ->label('Nama')
                    ->content(fn (?ExamPaymentReport $record): string => $record?->dosen ?? ''),
                Forms\Components\Checkbox::make('pns')
                    ->label('ASN')
                    ->default(fn (?ExamPaymentReport $record): bool => (int) $record?->status === 1),
                Forms\Components\Select::make('golongan')
                    ->options(array_combine(self::GOLONGANS, self::GOLONGANS)),
                Forms\Components\Select::make('jabatan_akademik')
                    ->label('Jabatan')
                    ->options(array_combine(self::JABATAN_AKADEMIKS, self::JABATAN_AKADEMIKS)),
                Forms\Components\Select::make('pendidikan')
                    ->options(array_combine(self::PENDIDIKANS, self::PENDIDIKANS)),
                Forms\Components\TextInput::make('npwp')
                    ->maxLength(255),
                Forms\Components\TextInput::make('rekening')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::baseColumns(includeStatus: true))
            ->filters([
                Tables\Filters\SelectFilter::make('report_date_id')
                    ->label('Periode')
                    ->options(fn (): array => ReportDate::orderByDesc('tanggal')->get()->mapWithKeys(
                        fn (ReportDate $rd) => [$rd->id => \Illuminate\Support\Carbon::parse($rd->tanggal)->format('Y-m-d').($rd->deskripsi ? ' - '.$rd->deskripsi : '')]
                    )->all()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([1 => 'ASN', 0 => 'Non ASN']),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->using(fn (ExamPaymentReport $record, array $data) => static::updateRecord($record, $data)),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus laporan honor ini?')
                    ->modalDescription('Baris laporan honor ini akan dihapus permanen.'),
            ])
            ->bulkActions([
                //
            ]);
    }

    /**
     * Dipakai bersama oleh table() di atas dan
     * ReportDateResource\Tables\PaymentSectionTable (slide-over "List Bayar
     * ASN"/"List Bayar Non-ASN" di baris ReportDateResource) - kolom Status
     * dibuang di slide-over itu karena konstan (sudah difilter per status).
     *
     * @return array<Tables\Columns\Column>
     */
    public static function baseColumns(bool $includeStatus): array
    {
        return [
            Tables\Columns\TextColumn::make('reportdate.tanggal')
                ->label('Periode')
                ->date(),
            Tables\Columns\TextColumn::make('departemen_id')
                ->label('Departemen'),
            Tables\Columns\TextColumn::make('dosen')
                ->label('Dosen')
                ->searchable(),
            ...($includeStatus ? [
                Tables\Columns\TextColumn::make('status_nama')
                    ->label('Status'),
            ] : []),
            Tables\Columns\TextColumn::make('golongan_nama')
                ->label('Gol'),
            Tables\Columns\TextColumn::make('npwp'),
            Tables\Columns\TextColumn::make('rekening'),
            Tables\Columns\TextColumn::make('jabatan_akademik'),
            Tables\Columns\TextColumn::make('pendidikan'),
            Tables\Columns\TextColumn::make('jumlah_honor_pembimbing')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('jumlah_honor_penguji_skripsi')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('jumlah_honor_penguji_proposal')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('jumlah_honor_penguji_seminar')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('total_honor')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('potong_pajak')
                ->label('Pajak')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('honor_dibayar')
                ->label('Jumlah')
                ->money('idr', divideBy: 1),
        ];
    }

    /**
     * Dipakai bersama oleh EditAction di table() di atas dan di
     * PaymentSectionTable, supaya logikanya tidak digandakan.
     */
    public static function updateRecord(ExamPaymentReport $record, array $data): ExamPaymentReport
    {
        $examPayment = ExamPayment::where('jabatan_akademik', $data['jabatan_akademik'] ?? null)
            ->where('pendidikan', $data['pendidikan'] ?? null)
            ->first();

        if (! $examPayment) {
            Notification::make()
                ->title('Data honor untuk jabatan akademik "'.($data['jabatan_akademik'] ?? '').'" dan pendidikan "'.($data['pendidikan'] ?? '').'" belum diatur di data honor ujian.')
                ->warning()
                ->send();

            throw new Halt();
        }

        $data['status'] = $data['pns'] ?? false ? 1 : 0;
        $data['honor_pembimbing'] = $examPayment->honor;
        unset($data['pns']);

        $record->update($data);

        return $record;
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
            'index' => Pages\ListExamPaymentReports::route('/'),
        ];
    }
}
