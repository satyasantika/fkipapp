<?php

namespace App\Filament\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

/**
 * Login kustom - field "username" (bukan email) dengan deteksi email-vs-
 * username seperti LoginController lama (sudah dihapus), plus tampilan lewat
 * $view kustom sendiri (bukan lewat {{ $this->form }} bawaan Filament - lihat
 * resources/views/filament/pages/auth/login.blade.php). Didaftarkan di
 * routes/web.php sebagai route bernama "login" di /login (bukan /admin/login),
 * dengan middleware panel admin dipasang manual supaya context Filament tetap
 * tersedia di luar prefix panel.
 *
 * Method/properti lain (getForms/form/getEmailFormComponent/dst) sengaja
 * TIDAK disentuh - dibiarkan warisan dari induk, tidak dipakai oleh view
 * kustom ini, jadi aman ditinggal apa adanya (lebih sedikit yang diubah,
 * lebih kecil risiko).
 */
class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    /**
     * View kita sudah dokumen HTML utuh (<!doctype>...</html>) sendiri -
     * override ini supaya TIDAK ikut dibungkus layout bawaan Filament
     * (yang punya <html> sendiri + kelas fi-simple-main-ctn/latar #FAFAFA),
     * yang kalau dibiarkan menghasilkan dua dokumen HTML bersarang.
     */
    protected static string $layout = 'filament.layouts.blank';

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->data;

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login = $data['username'] ?? '';
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $field => $login,
            'password' => $data['password'] ?? '',
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.username' => trans('auth.failed'),
        ]);
    }
}
