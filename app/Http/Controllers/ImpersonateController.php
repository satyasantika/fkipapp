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
        Auth::login($user);
        request()->session()->regenerate();

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

        Auth::loginUsingId($impersonatorId);
        request()->session()->regenerate();

        return redirect(\App\Filament\Resources\UserResource::getUrl())->with('success', 'Kembali ke akun admin.');
    }
}
