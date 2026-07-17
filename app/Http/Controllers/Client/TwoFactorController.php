<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('client.two-factor.index', [
            'enabled' => $user->two_factor_enabled,
            'confirmed' => ! is_null($user->two_factor_confirmed_at),
        ]);
    }

    public function showSetup()
    {
        $user = Auth::user();
        $service = new TwoFactorService;

        if ($user->two_factor_enabled) {
            return redirect()->route('client.two-factor.index');
        }

        $secret = $service->generateSecret();

        $user->update(['two_factor_secret' => $secret]);

        $qrCode = $service->getQrCodeUrl($secret, $user->email);
        $secret = $secret;

        return view('client.two-factor.setup', compact('qrCode', 'secret'));
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $secret = $user->two_factor_secret;
        if (! $secret || $user->two_factor_enabled) {
            return redirect()->route('client.two-factor.show-setup');
        }

        $service = new TwoFactorService;

        if (! $service->verifyCode($secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code']);
        }

        $user->update([
            'two_factor_confirmed_at' => now(),
            'two_factor_enabled' => true,
        ]);

        return redirect()->route('client.two-factor.index')
            ->with('success', 'Two-factor authentication has been enabled');
    }

    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()->route('client.two-factor.index')
            ->with('success', 'Two-factor authentication has been disabled');
    }
}
