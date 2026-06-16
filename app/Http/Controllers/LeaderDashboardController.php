<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Project;
use Inertia\Inertia;
use App\Models\Notification;

class LeaderDashboardController extends Controller
{
    public function index()
    {
       $user = auth()->user();

$leader = Member::where('email', $user->email)->first();

$notifications = Notification::where('user_id', $leader->id)
    ->where('workspace_id', session('workspace_id'))
    ->latest()
    ->get();
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
        $teamMembers = $leader->teamMembers ?? collect();

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
        $notifications = Notification::where('user_id', $user->id)
            ->where('workspace_id', session('workspace_id'))
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'message', 'is_read', 'created_at']);
        return Inertia::render(
            'Leader/Dashboard',
            [
                'leader' => $leader,

                'projects' => $projects,

                'teamMembers' => $teamMembers,
                'notifications' => $notifications,
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
