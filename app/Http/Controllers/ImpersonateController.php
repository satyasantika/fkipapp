<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonateController extends Controller
{
    /**
     * Log in as the given user, remembering who the real admin is.
     */
    public function take(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat impersonate diri sendiri.');
        abort_if($user->hasRole('admin'), 403, 'Tidak dapat impersonate sesama admin.');

        Log::info('Admin impersonation started', [
            'admin_id' => auth()->id(),
            'target_user_id' => $user->id,
        ]);

        session(['impersonator_id' => auth()->id()]);
        // Tidak perlu session()->regenerate() manual - Auth::login() sudah
        // memanggilnya sendiri (SessionGuard::updateSession() -> migrate(true)).
        Auth::login($user);
        $this->refreshPasswordHashInSession($user);

        return redirect(\Filament\Facades\Filament::getUrl())->with('success', 'Anda sekarang login sebagai '.$user->name);
    }

    /**
     * Return to the real admin account.
     */
    public function leave()
    {
        $impersonatorId = session()->pull('impersonator_id');
        abort_unless($impersonatorId, 403);

        Log::info('Admin impersonation ended', [
            'admin_id' => $impersonatorId,
            'target_user_id' => auth()->id(),
        ]);

        $admin = Auth::loginUsingId($impersonatorId);
        if ($admin) {
            $this->refreshPasswordHashInSession($admin);
        }

        return redirect(\App\Filament\Resources\UserResource::getUrl())->with('success', 'Kembali ke akun admin.');
    }

    /**
     * Tombol impersonate dipanggil dari action Livewire di panel Filament,
     * yang jalan lewat route POST /livewire/update - route generik Livewire
     * yang HANYA memakai middleware grup 'web' biasa, TIDAK PERNAH melalui
     * Filament\Http\Middleware\AuthenticateSession (lihat php artisan
     * route:list --path=livewire -vv, dibandingkan dengan route panel
     * seperti /admin yang memang melewatinya).
     *
     * Middleware itulah yang biasanya menyimpan ulang 'password_hash_web' di
     * session setiap kali Auth::login() dipanggil. Karena tidak pernah jalan
     * di sini, session tetap menyimpan hash password user SEBELUMNYA (mis.
     * admin) walau Auth::login() di atas sudah benar mengganti identitas ke
     * user baru. Begitu browser pindah ke halaman panel Filament yang
     * sungguhan (yang MEMANG melalui AuthenticateSession), hash lama vs hash
     * user baru dianggap tidak cocok -> dipaksa logout ke /admin/login,
     * padahal proses impersonate-nya sendiri sudah berhasil.
     */
    protected function refreshPasswordHashInSession(User $user): void
    {
        session(['password_hash_'.config('auth.defaults.guard') => $user->getAuthPassword()]);
    }
}
