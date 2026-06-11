<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeamRequest;
use Illuminate\Support\Facades\Auth;

class TeamRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:add,remove',
            'member_email' => 'required|email',
            'department' => 'nullable|string',
            'eligibility' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $user = Auth::user();

        TeamRequest::create([
            'workspace_id' => session('workspace_id') ?? 1,
            'leader_id' => $user->id,
            'type' => $request->type,
            'member_email' => $request->member_email,
            'department' => $request->department,
            'eligibility' => $request->eligibility,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return back();
    }
  

public function index()
{
    $user = Auth::user();

    $requests = TeamRequest::where('leader_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return Inertia::render('Leader/Team', [
        'requests' => $requests
    ]);
}
}
