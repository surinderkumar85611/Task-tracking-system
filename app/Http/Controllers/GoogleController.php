<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $inviteEmail = session('invite_email');
        $workspaceId = session('invite_workspace_id');
        $role = session('invite_role');
        $department = session('invite_department');

        // If invite exists, enforce email match
        if ($inviteEmail && $inviteEmail !== $googleUser->email) {
            return redirect('/login')->withErrors([
                'email' => 'This Google account does not match the invited email.'
            ]);
        }

        $user = User::updateOrCreate(
            ['email' => $googleUser->email],
            [
                'name' => $googleUser->name,
                'google_id' => $googleUser->id,

                'workspace_id' => $workspaceId,
                'role' => $role,
                'department' => $department,

                'email_verified_at' => now(),
            ]
        );

        Auth::login($user);

        session()->forget([
            'invite_email',
            'invite_workspace_id',
            'invite_role',
            'invite_department',
        ]);

        return redirect('/dashboard');
    }
}