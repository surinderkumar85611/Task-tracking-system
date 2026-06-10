<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LProjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get logged-in leader
        $leader = Member::where('email', $user->email)->first();

        if (!$leader || $leader->role !== 'TL') {
            abort(403, 'Access Denied');
        }

        $projects = Project::with([
            'tasks',
            'teamLeader',
            'teamLeader.teamMembers' // Load all members under the leader
        ])
        ->where('team_leader_id', $leader->id)
        ->when(session('workspace_id'), function ($query) {
            $query->where('workspace_id', session('workspace_id'));
        })
        ->get();

        return Inertia::render('Leader/Projects', [
            'projects' => $projects,

            'teamLeaders' => Member::with('teamMembers')
                ->where('role', 'TL')
                ->get(),
        ]);
    }
}