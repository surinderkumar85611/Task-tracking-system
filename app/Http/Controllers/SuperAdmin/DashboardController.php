<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Project;
use App\Models\Member;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $projects = Project::with(['teamLeader', 'workspace'])->get();

        // ---- allProjects: flat list the Vue page filters/searches/charts ----
        $allProjects = $projects->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'progress' => (int) $p->progress,
                'deadline' => optional($p->deadline)->format('Y-m-d') ?? $p->deadline,
                'created_at' => optional($p->created_at)->toDateString(),
                'workspace_id' => $p->workspace_id,
                'workspace_name' => $p->workspace->name ?? null,
                'team_leader_id' => $p->team_leader_id,
                'team_leader_name' => $p->teamLeader
                    ? trim($p->teamLeader->first_name . ' ' . $p->teamLeader->last_name)
                    : null,
            ];
        })->values();

        // ---- teams: one entry per TL member, with real project + member counts ----
        $teamLeaders = Member::where('role', 'TL')->with(['teamMembers', 'workspace'])->get();

        $teams = $teamLeaders->map(function ($tl) use ($projects) {
            $tlProjects = $projects->where('team_leader_id', $tl->id)->values();
            $total = $tlProjects->count();
            $completed = $tlProjects->where('progress', '>=', 100)->count();

            return [
                'id' => $tl->id,
                'name' => trim($tl->first_name . ' ' . $tl->last_name),
                'workspace_id' => $tl->workspace_id,
                'workspace_name' => $tl->workspace->name ?? '—',
                'members_count' => $tl->teamMembers->count(),
                'projects_count' => $total,
                'completed_count' => $completed,
                'completion_rate' => $total ? (int) round($completed / $total * 100) : 0,
                'members' => $tl->teamMembers->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => trim($m->first_name . ' ' . $m->last_name),
                    'department' => $m->department,
                    'role' => $m->role,
                ])->values(),
                'projects' => $tlProjects->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'progress' => (int) $p->progress,
                ])->values(),
            ];
        })->values();

        // ---- allMembers: for the "add member to team" dropdown in the team modal ----
        $allMembers = Member::select('id', 'first_name', 'last_name', 'email', 'workspace_id', 'role', 'assigned_to')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => trim($m->first_name . ' ' . $m->last_name),
                'email' => $m->email,
                'workspace_id' => $m->workspace_id,
                'role' => $m->role,
                'assigned_to' => $m->assigned_to,
            ]);

        // ---- stats: keys matched to what Dashboard.vue actually reads ----
        $totalProjects = $projects->count();
        $completedProjects = $projects->where('progress', '>=', 100)->count();

        $stats = [
            'projects' => $totalProjects,
            'completedProjects' => $completedProjects,
            'pendingProjects' => $totalProjects - $completedProjects,
            'completionRate' => $totalProjects ? (int) round($completedProjects / $totalProjects * 100) : 0,
            'totalTeams' => $teamLeaders->count(),
            'teamLeaders' => $teamLeaders->count(),
            'workspaces' => Workspace::count(),
        ];

        return Inertia::render('SuperAdmin/Dashboard', [
            'stats' => $stats,

            'admins' => User::where('role', 'ADMIN')
                ->select('id', 'name', 'email', 'created_at')
                ->latest()
                ->get(),

            'teams' => $teams,
            'allProjects' => $allProjects,
            'allMembers' => $allMembers,

            // No activity-log table exists in your schema yet, so this stays empty
            // until you add one (e.g. an `activities` table logging project/task/member events).
            'recentActivity' => [],
        ]);
    }
}