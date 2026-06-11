<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function generateSecret()
{
    $google2fa = new Google2FA();
    $user = Auth::user();

    if (!$user->two_factor_secret) {
        $user->two_factor_secret = $google2fa->generateSecretKey();
        $user->save();
    }

    $secret = $user->two_factor_secret;

    // This outputs the raw text stream that your new Vue component reads to draw the QR code
    $qr = "otpauth://totp/" . config('app.name') . ":" . $user->email .
        "?secret=" . $secret .
        "&issuer=" . config('app.name') .
        "&algorithm=SHA1&digits=6&period=30";

    return response()->json([
        'secret' => $secret,
        'qr' => $qr
    ]);
}

    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $user = Auth::user();

        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid code'
            ], 422);
        }

        $user->two_factor_enabled = true;
        $user->save();

        return response()->json([
            'message' => '2FA enabled successfully'
        ]);
    }

    public function disable(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $user = Auth::user();

        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid code'
            ], 422);
        }

        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->save();

        return response()->json([
            'message' => '2FA disabled successfully'
        ]);
    }
}