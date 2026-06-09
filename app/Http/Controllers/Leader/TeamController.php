<?php
namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function index()
    {
        $workspaceId = session('workspace_id') ?? 1;

        // 🔥 STEP 1: get logged-in user
        $user = Auth::user();

        // ⚠️ IMPORTANT: convert user → member (because your system uses members table)
        $leader = Member::where('email', $user->email)
            ->where('workspace_id', $workspaceId)
            ->first();

        if (!$leader) {
            return Inertia::render('Leader/Team', [
                'teamLeaders' => [],
                'members' => [],
            ]);
        }

        // 🔥 STEP 2: ONLY members assigned to this leader
        $members = Member::where('workspace_id', $workspaceId)
            ->where('assigned_to', $leader->id)
            ->with('assignedTasks')
            ->get();

        // 🔥 STEP 3: leader data
        $teamLeader = Member::where('id', $leader->id)
            ->with(['teamMembers.assignedTasks'])
            ->first();

        return Inertia::render('Leader/Team', [
            'teamLeaders' => [$teamLeader], // single leader only
            'members' => $members,
        ]);
    }
}