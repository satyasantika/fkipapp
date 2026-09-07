<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // Alamat utama login sekarang /login (lihat routes/web.php), tapi
            // action di sini TETAP class Login (bukan closure redirect) -
            // Filament cuma mendaftarkan sebuah Login page sebagai komponen
            // Livewire (registerLivewireComponents() di HasComponents.php)
            // kalau aksinya adalah class komponen sungguhan. Kalau diganti
            // closure, pendaftaran itu tidak terjadi sama sekali, dan submit
            // form di /login akan gagal dengan
            // Livewire\Exceptions\ComponentNotFoundException (nama komponen
            // "app.filament.auth.login" tidak pernah terdaftar). /admin/login
            // jadi tetap berfungsi (duplikat harmless dari halaman yang sama)
            // - lebih aman daripada mengorbankan pendaftaran komponennya.
            ->login(\App\Filament\Auth\Login::class)
            ->authGuard('web')
            // Warna & font disamakan dengan tema halaman login (lihat
            // resources/views/filament/pages/auth/login.blade.php) - teal
            // sebagai primary (persis --teal-600 di sana) dan emerald untuk
            // aksen "success", supaya identitas visualnya konsisten dari
            // pintu masuk sampai ke seluruh panel, bukan cuma di /login.
            ->colors([
                'primary' => Color::Teal,
                'success' => Color::Emerald,
            ])
            ->font('Manrope')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            // Tombol "kembali ke akun admin" saat sedang impersonate - render
            // hook resmi Filament ini persis dipanggil sebelum dropdown avatar
            // (fi-user-avatar) di topbar, jadi tombolnya muncul di kiri avatar.
            // View-nya sendiri yang mengecek session('impersonator_id') supaya
            // hanya tampil saat benar-benar sedang impersonate.
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => view('filament.leave-impersonation-button')->render(),
            )
            // Font judul (Fraunces) yang sama dengan halaman login, dipasang
            // lewat override CSS kecil di STYLES_AFTER (bukan lewat ->font(),
            // yang cuma bisa satu font untuk seluruh panel) supaya heading
            // tetap beda dari body text (Manrope) sama seperti di /login.
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => view('filament.theme-overrides')->render(),
            );
    }
}
