<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Invitation;
use App\Models\Member;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z\s]+$/'],
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/'
            ],
        ], [
            'name.regex' => 'Name should only contain letters.',
            'password.regex' => 'Password must include uppercase, lowercase, number and special character.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Account created successfully');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            session()->forget('workspace_id');

            $member = Member::where(
                'email',
                auth()->user()->email
            )->first();

            if ($member && $member->role === 'TL') {
                return redirect('/leader-dashboard');
            }

            return redirect('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials',
        ]);
    }
    public function showCompleteProfile()
    {
        $inviteToken = session('invite_token');

        if (!$inviteToken) {
            return redirect('/login');
        }

        $invite = Invitation::where('token', $inviteToken)
            ->whereNull('accepted_at')
            ->first();

        if (!$invite) {
            return redirect('/login');
        }

        return Inertia::render('Auth/CompleteProfile', [
            'email' => $invite->email,
            'role' => $invite->role,
            'department' => $invite->department,
            'workspace_id' => $invite->workspace_id,
        ]);
    }
    public function completeProfile(Request $request)
    {
        $inviteToken = session('invite_token');

        if (!$inviteToken) {
            return redirect('/login');
        }

        $invite = Invitation::where('token', $inviteToken)
            ->whereNull('accepted_at')
            ->first();

        if (!$invite) {
            return redirect('/login');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z\s]+$/'
            ],

            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/'
            ],
        ], [
            'name.regex' => 'Name should only contain letters.',
            'password.regex' => 'Password must include uppercase, lowercase, number and special character.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $invite->email,
            'password' => Hash::make($request->password),

            'workspace_id' => $invite->workspace_id,
            'role' => $invite->role,
            'department' => $invite->department,
        ]);
        Member::create([
            'workspace_id' => $invite->workspace_id,
            'first_name'   => $request->name,
            'last_name'    => '',
            'email'        => $invite->email,
            'phone'        => '',
            'department'   => $invite->department,
            'role'         => $invite->role,
            'assigned_to'  => null,
        ]);
        $invite->update([
            'accepted_at' => now(),
        ]);


        session()->forget([
            'invite_token',
            'invite_email',
            'invite_workspace_id',
            'invite_role',
            'invite_department',
        ]);

        return redirect('/login')
            ->with('success', 'Profile completed successfully. Please login.');
    }

    public function showTwoFactorChallenge()
    {
        if (!session()->has('auth.password_confirmed_at') && !Auth::check()) {
            return redirect('/login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function verifyTwoFactorChallenge(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = $request->user() ?? User::find(session('login.id'));

        if (!$user) {
            return redirect('/login')->withErrors(['email' => 'Session expired. Please log in again.']);
        }

        $google2fa = new \Pragmarx\Google2FA\Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            if (!Auth::check()) {
                Auth::login($user);
            }

            session()->forget('login.id');

            return redirect('/dashboard');
        }

        return back()->withErrors([
            'code' => 'The security code you entered is incorrect or has expired.',
        ]);
    }
}
