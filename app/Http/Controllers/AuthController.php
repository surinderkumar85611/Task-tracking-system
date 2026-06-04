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

            //    return back()->with('success', 'Login successful');
            session()->forget('workspace_id');
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
}
