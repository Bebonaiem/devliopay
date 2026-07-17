<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TwoFactorChallengeController extends Controller
{
    public function showForm()
    {
        if (! session('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = session('2fa_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (! $user || ! $user->two_factor_enabled) {
            session()->forget('2fa_user_id');
            return redirect()->route('login');
        }

        $service = new TwoFactorService;

        if (! $service->verifyCode($user->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        session()->forget('2fa_user_id');

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->is_admin) {
            return redirect()->intended('/admin');
        }

        return redirect()->intended('/client');
    }
}
