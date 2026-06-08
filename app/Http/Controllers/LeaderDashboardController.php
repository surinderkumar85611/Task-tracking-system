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

        // If logged-in user is not a member yet,
        // just use the first available member record.
        if (!$leader) {
            $leader = Member::first();
        }

        $projects = Project::with([
            'tasks',
            'teamLeader.teamMembers'
        ])
        ->where('workspace_id', session('workspace_id'))
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
            'LeaderDashboard',
            [
                'leader' => $leader,

                'projects' => $projects,

                'teamMembers' => $teamMembers,

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