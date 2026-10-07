<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;

class SettingsController extends Controller
{
    // the Super Admin logs in through the "super_admin" guard, not the default one
    private function admin()
    {
        return Auth::guard('super_admin')->user();
    }

    public function index()
    {
        $admin = $this->admin();

        return Inertia::render('SuperAdmin/Settings', [
            'profile' => [
                'name'               => $admin->name,
                'email'              => $admin->email,
                'two_factor_enabled' => (bool) $admin->two_factor_enabled,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $admin = $this->admin();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'min:2', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('super_admins', 'email')->ignore($admin->id),
            ],
        ]);

        $admin->name = $validated['name'];
        $admin->email = $validated['email'];
        $admin->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    /* ---------------- two factor authentication ---------------- */

    public function generateSecret()
    {
        $google2fa = new Google2FA();
        $admin = $this->admin();

        if (!$admin->two_factor_secret) {
            $admin->two_factor_secret = $google2fa->generateSecretKey();
            $admin->save();
        }

        return response()->json([
            'secret' => $admin->two_factor_secret,
            'qr'     => $google2fa->getQRCodeUrl(
                config('app.name'),
                $admin->email,
                $admin->two_factor_secret
            ),
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $admin = $this->admin();

        if (!$admin->two_factor_secret) {
            return response()->json(['message' => 'Generate a QR code first.'], 422);
        }

        $valid = (new Google2FA())->verifyKey($admin->two_factor_secret, $request->code);

        if (!$valid) {
            return response()->json(['message' => 'Invalid code'], 422);
        }

        $admin->two_factor_enabled = true;
        $admin->save();

        return response()->json(['message' => '2FA enabled successfully']);
    }

    public function disable(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $admin = $this->admin();

        $valid = $admin->two_factor_secret
            && (new Google2FA())->verifyKey($admin->two_factor_secret, $request->code);

        if (!$valid) {
            return response()->json(['message' => 'Invalid code'], 422);
        }

        $admin->two_factor_enabled = false;
        $admin->two_factor_secret = null;
        $admin->save();

        return response()->json(['message' => '2FA disabled successfully']);
    }
}