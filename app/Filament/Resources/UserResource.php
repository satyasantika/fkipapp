<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Http\Controllers\ImpersonateController;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'User';

    protected static ?string $modelLabel = 'User';

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
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('departement_id')
                    ->label('Jurusan')
                    ->relationship('departement', 'nama')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn (string $state): string => bcrypt($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->visibleOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Pengguna')
                    ->description(fn (User $record): string => '@'.$record->username)
                    ->searchable(['name', 'username']),
                Tables\Columns\TextColumn::make('departement.nama')
                    ->label('Jurusan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\Action::make('impersonate')
                    ->label('Impersonate')
                    ->icon('heroicon-o-user-circle')
                    ->color('warning')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->id !== auth()->id() && ! $record->hasRole('admin'))
                    // Custom Action::make() TIDAK pernah memanggil success() secara
                    // otomatis (beda dari CreateAction/EditAction/DeleteAction bawaan),
                    // jadi ->successRedirectUrl() TIDAK PERNAH terpicu di sini - itu
                    // sebabnya redirect sebelumnya kadang mendarat di /admin/login
                    // (auth state belum tentu konsisten saat Livewire "menebak" redirect
                    // dari return value closure). $action->redirect() adalah API resmi
                    // Livewire yang dijamin bekerja untuk action apa pun.
                    ->action(function (User $record, Tables\Actions\Action $action) {
                        app(ImpersonateController::class)->take($record);

                        Notification::make()
                            ->title('Anda sekarang login sebagai '.$record->name)
                            ->success()
                            ->send();

                        $action->redirect(\Filament\Facades\Filament::getUrl());
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Hapus pengguna yang dipilih?')
                        ->modalDescription('Semua akun pengguna yang dipilih akan dihapus permanen dan tidak bisa login lagi.'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
