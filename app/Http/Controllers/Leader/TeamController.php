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

        // 🔥 STEP 1: members (NO assignedTasks)
        $members = Member::where('workspace_id', $workspaceId)
            ->where('assigned_to', $leader->id)
            ->get();

        // 🔥 STEP 2: leader (NO assignedTasks)
        $teamLeader = Member::where('id', $leader->id)
            ->with('teamMembers')
            ->first();

        // 🔥 STEP 3: ADD PROJECTS (THIS IS THE KEY FIX)
        $projects = Project::where('workspace_id', $workspaceId)
            ->with('tasks')
            ->get();

        return Inertia::render('Leader/Team', [
            'teamLeaders' => [$teamLeader],
            'members' => $members,
            'projects' => $projects, // ✅ THIS FIXES YOUR UI
        ]);
    }
}