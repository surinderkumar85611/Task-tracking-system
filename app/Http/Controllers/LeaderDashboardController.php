<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Project;
use Inertia\Inertia;

class LeaderDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // TEMPORARY BYPASS
        $leader = Member::where(
            'email',
            $user->email
        )->first();

        if (!$leader || $leader->role !== 'TL') {
            abort(403, 'Access Denied');
        }
        $projects = Project::with([
            'tasks',
            'teamLeader',
        ])
            ->when(session('workspace_id'), function ($query) {
                $query->where('workspace_id', session('workspace_id'));
            })
            ->where('team_leader_id', $leader->id)
            ->get();
        $teamMembers = $leader
            ? $leader->teamMembers
            : collect();

        $allTasks = collect();

        foreach ($projects as $project) {
            $allTasks = $allTasks->merge(
                $project->tasks
            );
        }

        $completedTasks = $allTasks
            ->where('status', 'Completed')
            ->count();

        $pendingTasks = $allTasks
            ->where('status', '!=', 'Completed')
            ->count();

        $statusBreakdown = [
            'Todo' => $allTasks
                ->where('status', 'Todo')
                ->count(),

            'In Progress' => $allTasks
                ->where('status', 'In Progress')
                ->count(),

            'Completed' => $allTasks
                ->where('status', 'Completed')
                ->count(),
        ];

        return Inertia::render(
            'Leader/Dashboard',
            [
                'leader' => $leader,

                'projects' => $projects,

                'teamMembers' => $teamMembers,

                'currentWorkspaceId' => session('workspace_id'),

                'stats' => [
                    'projects' => $projects->count(),
                    'tasks' => $allTasks->count(),
                    'completed' => $completedTasks,
                    'pending' => $pendingTasks,
                ],

                'statusBreakdown' => $statusBreakdown,
            ]
        );
    }
}
