<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function index()
    {
        $workspaceId = session('workspace_id') ?? 1;

        $user = Auth::user();

        $leader = Member::where('email', $user->email)
            ->where('workspace_id', $workspaceId)
            ->first();

        if (!$leader) {
            return Inertia::render('Leader/Team', [
                'teamLeaders' => [],
                'members' => [],
                'projects' => [],
            ]);
        }

        $members = Member::where('workspace_id', $workspaceId)
            ->where('assigned_to', $leader->id)
            ->get();

        $teamLeader = Member::where('id', $leader->id)
            ->with('teamMembers')
            ->first();

        $projects = Project::where('workspace_id', $workspaceId)
            ->with('tasks')
            ->get();

        return Inertia::render('Leader/Team', [
            'teamLeaders' => [$teamLeader],
            'members' => $members,
            'projects' => $projects,
        ]);
    }
}
