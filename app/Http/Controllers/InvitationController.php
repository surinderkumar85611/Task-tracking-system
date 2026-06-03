<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invitation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\InviteMemberMail;

class InvitationController extends Controller
{
    public function generate(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'role' => 'required|string',
        'department' => 'nullable|string',
        'workspace_id' => 'required|integer',
    ]);

    $existing = Invitation::where('email', $request->email)
        ->where('workspace_id', $request->workspace_id)
        ->whereNull('accepted_at')
        ->first();

    if ($existing) {
        return back()->withErrors([
            'email' => 'Invite already sent'
        ]);
    }

    $token = \Illuminate\Support\Str::random(40);

    $invite = new Invitation();
    $invite->email = $request->email;
    $invite->role = $request->role;
    $invite->department = $request->department;
    $invite->workspace_id = $request->workspace_id;
    $invite->token = $token;
    $invite->save();

    $link = url("/invite/accept/{$token}");

    \Mail::to($request->email)->send(
        new \App\Mail\InviteMemberMail($link)
    );

    return back()->with([
        'success' => 'Invitation sent successfully',
        'invite_link' => $link
    ]);
}

 public function accept($token)
{
    $invite = Invitation::where('token', $token)
        ->whereNull('accepted_at')
        ->first();

    if (!$invite) {
        return abort(404, 'Invalid or expired invite');
    }

    session([
        'invite_token' => $token,
        'invite_email' => $invite->email,
        'invite_workspace_id' => $invite->workspace_id,
        'invite_role' => $invite->role,
        'invite_department' => $invite->department,
    ]);

    return redirect('/complete-profile');
}
}