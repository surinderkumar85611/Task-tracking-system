<?php
namespace App\Http\Controllers;

use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

public function login(Request $request)
{
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    $admin = SuperAdmin::where('email', $credentials['email'])->first();

    if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
        throw ValidationException::withMessages([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    // 2FA on: don't log in yet, hold the admin in a short-lived pending state
    if ($admin->two_factor_enabled) {
        $request->session()->put('super_admin_2fa', [
            'id'      => $admin->id,
            'expires' => now()->addMinutes(5)->timestamp,
        ]);

        return response()->json(['two_factor_required' => true]);
    }

    Auth::guard('super_admin')->login($admin);
    $request->session()->regenerate();

    return response()->json(['message' => 'Logged in']);
}

public function verifyTwoFactor(Request $request)
{
    $request->validate(['code' => ['required', 'digits:6']]);

    $pending = $request->session()->get('super_admin_2fa');

    if (! $pending || $pending['expires'] < now()->timestamp) {
        $request->session()->forget('super_admin_2fa');
        return response()->json(['message' => 'Session expired. Please sign in again.'], 422);
    }

    $admin = SuperAdmin::find($pending['id']);

    $valid = $admin
        && $admin->two_factor_secret
        && (new Google2FA())->verifyKey($admin->two_factor_secret, $request->code);

    if (! $valid) {
        return response()->json(['message' => 'Invalid authentication code'], 422);
    }

    $request->session()->forget('super_admin_2fa');
    Auth::guard('super_admin')->login($admin);
    $request->session()->regenerate();

    return response()->json(['message' => 'Logged in']);
}