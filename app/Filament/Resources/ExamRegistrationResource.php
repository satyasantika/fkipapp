<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamRegistrationResource\Pages;
use App\Filament\Resources\ExamRegistrationResource\RelationManagers;
use App\Models\ExamRegistration;
use App\Models\Lecture;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamRegistrationResource extends Resource
{
    protected static ?string $model = ExamRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Registrasi Ujian';

    protected static ?string $modelLabel = 'Registrasi Ujian';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('jurusan') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['exam_type', 'student', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2', 'penguji3']);

        if (auth()->user()?->hasRole('jurusan')) {
            $query->where('departement_id', auth()->user()->departement_id);
        }

        return $query;
    }

    public static function lectureOptionsQuery(): Builder
    {
        $query = Lecture::query()->orderBy('nama');

        if (auth()->user()?->hasRole('jurusan')) {
            $query->where('departement_id', auth()->user()->departement_id);
        }

        return $query;
    }

    public static function lectureOptionLabel(Lecture $lecture): string
    {
        return "{$lecture->nama} - {$lecture->departement_id}";
    }

    public static function examTypeDateColumn(?int $examTypeId): ?string
    {
        return match ($examTypeId) {
            1 => 'tanggal_proposal',
            2 => 'tanggal_seminar',
            3 => 'tanggal_skripsi',
            default => null,
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('dilaporkan_notice')
                    ->hiddenLabel()
                    ->content('Ujian ini sudah dilaporkan. Anda tidak dapat mengedit tanggal ujian dan susunan para penguji.')
                    ->visible(fn (?ExamRegistration $record): bool => (bool) $record?->dilaporkan),

                Forms\Components\Select::make('student_id')
                    ->label('Mahasiswa')
                    ->relationship('student', 'nama')
                    ->searchable()
                    ->required()
                    ->visibleOn('create'),
                Forms\Components\Placeholder::make('student_display')
                    ->label('Mahasiswa')
                    ->content(fn (?ExamRegistration $record): string => $record?->student?->nama ?? '')
                    ->visibleOn('edit'),

                Forms\Components\Select::make('exam_type_id')
                    ->label('Jenis Ujian')
                    ->relationship('exam_type', 'nama_ujian')
                    ->disabled(fn (?ExamRegistration $record): bool => (bool) $record?->dilaporkan),
                Forms\Components\Select::make('ujian_ke')
                    ->label('Ujian Ke-')
                    ->options([1 => 1, 2 => 2, 3 => 3])
                    ->required(),
                Forms\Components\DatePicker::make('tanggal_ujian')
                    ->label('Tanggal Ujian')
                    ->disabled(fn (?ExamRegistration $record): bool => (bool) $record?->dilaporkan),
                Forms\Components\TimePicker::make('waktu_mulai')
                    ->label('Mulai Ujian'),
                Forms\Components\TimePicker::make('waktu_akhir')
                    ->label('Akhir Ujian'),
                Forms\Components\Select::make('ruangan')
                    ->label('Tempat Ujian')
                    ->options([
                        1 => 'Ruang Sidang 1',
                        2 => 'Ruang Sidang 2',
                        3 => 'Ruang Sidang 3',
                        4 => 'Ruang Sidang 4',
                    ]),
                Forms\Components\Textarea::make('judul_penelitian')
                    ->label('Judul Penelitian')
                    ->rows(5)
                    ->autosize()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ipk')
                    ->label('IPK')
                    ->numeric()
                    ->minValue(2.00)
                    ->maxValue(4.00)
                    ->step(0.01),

                Forms\Components\Checkbox::make('pembimbing1_dibayar')
                    ->label('Pembimbing 1 dibayar')
                    ->visibleOn('edit'),
                Forms\Components\Select::make('pembimbing1_id')
                    ->label('Pembimbing 1')
                    ->relationship('pembimbing1', 'nama', modifyQueryUsing: fn (Builder $query) => self::lectureOptionsQuery())
                    ->getOptionLabelFromRecordUsing(fn (Lecture $record) => self::lectureOptionLabel($record))
                    ->searchable()
                    ->disabled(fn (?ExamRegistration $record): bool => (bool) $record?->dilaporkan)
                    ->visibleOn('edit'),

                Forms\Components\Checkbox::make('pembimbing2_dibayar')
                    ->label('Pembimbing 2 dibayar')
                    ->visibleOn('edit'),
                Forms\Components\Select::make('pembimbing2_id')
                    ->label('Pembimbing 2')
                    ->relationship('pembimbing2', 'nama', modifyQueryUsing: fn (Builder $query) => self::lectureOptionsQuery())
                    ->getOptionLabelFromRecordUsing(fn (Lecture $record) => self::lectureOptionLabel($record))
                    ->searchable()
                    ->disabled(fn (?ExamRegistration $record): bool => (bool) $record?->dilaporkan)
                    ->visibleOn('edit'),

                Forms\Components\Checkbox::make('penguji1_dibayar')
                    ->label('Penguji 1 dibayar')
                    ->visibleOn('edit'),
                Forms\Components\Select::make('penguji1_id')
                    ->label('Penguji 1')
                    ->relationship('penguji1', 'nama', modifyQueryUsing: fn (Builder $query) => self::lectureOptionsQuery())
                    ->getOptionLabelFromRecordUsing(fn (Lecture $record) => self::lectureOptionLabel($record))
                    ->searchable()
                    ->disabled(fn (?ExamRegistration $record): bool => (bool) $record?->dilaporkan)
                    ->visibleOn('edit'),

                Forms\Components\Checkbox::make('penguji2_dibayar')
                    ->label('Penguji 2 dibayar')
                    ->visibleOn('edit'),
                Forms\Components\Select::make('penguji2_id')
                    ->label('Penguji 2')
                    ->relationship('penguji2', 'nama', modifyQueryUsing: fn (Builder $query) => self::lectureOptionsQuery())
                    ->getOptionLabelFromRecordUsing(fn (Lecture $record) => self::lectureOptionLabel($record))
                    ->searchable()
                    ->disabled(fn (?ExamRegistration $record): bool => (bool) $record?->dilaporkan)
                    ->visibleOn('edit'),

                Forms\Components\Checkbox::make('penguji3_dibayar')
                    ->label('Penguji 3 dibayar')
                    ->visibleOn('edit'),
                Forms\Components\Select::make('penguji3_id')
                    ->label('Penguji 3')
                    ->relationship('penguji3', 'nama', modifyQueryUsing: fn (Builder $query) => self::lectureOptionsQuery())
                    ->getOptionLabelFromRecordUsing(fn (Lecture $record) => self::lectureOptionLabel($record))
                    ->searchable()
                    ->disabled(fn (?ExamRegistration $record): bool => (bool) $record?->dilaporkan)
                    ->visibleOn('edit'),

                Forms\Components\Select::make('ketuapenguji_id')
                    ->label('Ketua Penguji')
                    ->options(fn (): array => self::lectureOptionsQuery()->get()->mapWithKeys(
                        fn (Lecture $lecture) => [$lecture->id => self::lectureOptionLabel($lecture)]
                    )->all())
                    ->searchable()
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isJurusan = auth()->user()?->hasRole('jurusan') ?? false;

        $sharedColumns = [
            Tables\Columns\TextColumn::make('student.nama')
                ->label('Mahasiswa')
                ->description(fn (ExamRegistration $record): ?string => $record->student?->nim)
                // Nama BARE (bukan 'student.nim'/'student.nama') - kolom ini
                // sendiri sudah dot-path ('student.nama'), jadi Filament SUDAH
                // otomatis mendeteksi relasi 'student' dan membungkus pencarian
                // lewat whereRelation(). Menambahkan prefix 'student.' lagi di
                // sini dulu bikin double-prefix (whereRelation('student',
                // 'student.nim', ...)) - kolom di dalam relasi tidak ada yang
                // namanya 'student.nim', makanya SQL error "Unknown column
                // 'student.nim'" dan pencarian selalu gagal.
                ->searchable(['nim', 'nama'])
                ->sortable(),
            Tables\Columns\TextColumn::make('pembimbing')
                ->label('Pembimbing')
                ->getStateUsing(fn (ExamRegistration $record): array => collect([
                    $record->pembimbing1?->nama,
                    $record->pembimbing2?->nama,
                ])->filter()->values()->all())
                ->listWithLineBreaks()
                ->bulleted()
                // Kolom virtual ('pembimbing' bukan relasi asli - relasinya
                // pembimbing1/pembimbing2), jadi Filament TIDAK bisa otomatis
                // mendeteksi relasinya dari nama kolom seperti kasus 'student.
                // nama' di atas - ->searchable([array kolom]) di kolom virtual
                // begini menghasilkan referensi kolom langsung tanpa join sama
                // sekali (`pembimbing1`.`nama` di WHERE tanpa JOIN), juga error.
                // ->searchQuery() dipakai supaya query pencariannya ditulis
                // manual & benar.
                ->searchable(query: function (Builder $query, string $search): Builder {
                    return $query->where(function (Builder $q) use ($search) {
                        $q->orWhereHas('pembimbing1', fn (Builder $r) => $r->where('nama', 'like', "%{$search}%"))
                            ->orWhereHas('pembimbing2', fn (Builder $r) => $r->where('nama', 'like', "%{$search}%"));
                    });
                }),
            Tables\Columns\TextColumn::make('penguji')
                ->label('Penguji')
                ->getStateUsing(fn (ExamRegistration $record): array => collect([
                    $record->penguji1?->nama,
                    $record->penguji2?->nama,
                    $record->penguji3?->nama,
                ])->filter()->values()->all())
                ->listWithLineBreaks()
                ->bulleted()
                ->searchable(query: function (Builder $query, string $search): Builder {
                    return $query->where(function (Builder $q) use ($search) {
                        $q->orWhereHas('penguji1', fn (Builder $r) => $r->where('nama', 'like', "%{$search}%"))
                            ->orWhereHas('penguji2', fn (Builder $r) => $r->where('nama', 'like', "%{$search}%"))
                            ->orWhereHas('penguji3', fn (Builder $r) => $r->where('nama', 'like', "%{$search}%"));
                    });
                }),
        ];

        $statusColumn = Tables\Columns\TextColumn::make('dilaporkan')
            ->label('Status')
            ->badge()
            ->formatStateUsing(fn (bool $state): string => $state ? 'Sudah Dilaporkan' : 'Belum Dilaporkan')
            ->color(fn (bool $state): string => $state ? 'success' : 'gray');

        $examTypeColumn = Tables\Columns\TextColumn::make('exam_type.singkat_ujian')
            ->label('Ujian')
            ->badge()
            ->color(fn (?string $state): string => match ($state) {
                'sempro' => 'gray',
                'semhas' => 'info',
                'sidang' => 'primary',
                default => 'gray',
            });

        $columns = $isJurusan ? [
            $statusColumn,
            Tables\Columns\TextColumn::make('tanggal_ujian')
                ->date(),
            Tables\Columns\TextColumn::make('ruangan'),
            Tables\Columns\TextColumn::make('waktu_mulai')
                ->label('Waktu')
                ->formatStateUsing(fn (ExamRegistration $record): string => $record->waktu_mulai
                    ? substr($record->waktu_mulai, 0, 5).' - '.substr($record->waktu_akhir, 0, 5)
                    : ''),
            $examTypeColumn,
            ...$sharedColumns,
        ] : [
            $examTypeColumn,
            Tables\Columns\TextColumn::make('tanggal_ujian')
                ->label('Diujiankan')
                ->date(),
            $statusColumn,
            ...$sharedColumns,
        ];

        return $table
            ->columns($columns)
            ->filters([
                //
            ])
            ->actions([
                //
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
            'index' => Pages\ListExamRegistrations::route('/'),
        ];
    }
}
