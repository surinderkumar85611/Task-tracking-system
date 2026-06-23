<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Member;
use App\Models\Task;
use App\Models\DashboardWidget;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $workspaceId = session('workspace_id');

        if (!$workspaceId) {

            return Inertia::render('Dashboard', [
                'widgets' => [],
                'projects' => [],
                'members' => [],
                'stats' => [
                    'totalProjects' => 0,
                    'teamMembers' => 0,
                    'completedTasks' => 0,
                    'pendingTasks' => 0,
                ]
            ]);
        }

        $widgets = DashboardWidget::where(
            'workspace_id',
            $workspaceId
        )
            ->where('active', true)
            ->orderBy('position')
            ->get();

        if ($widgets->isEmpty()) {

            DashboardWidget::insert([
                [
                    'workspace_id' => $workspaceId,
                    'title' => 'Total Projects',
                    'widget_type' => 'total_projects',
                    'position' => 1,
                    'active' => true,
                ],
                [
                    'workspace_id' => $workspaceId,
                    'title' => 'Team Members',
                    'widget_type' => 'team_members',
                    'position' => 2,
                    'active' => true,
                ],
                [
                    'workspace_id' => $workspaceId,
                    'title' => 'Completed Tasks',
                    'widget_type' => 'completed_tasks',
                    'position' => 3,
                    'active' => true,
                ],
                [
                    'workspace_id' => $workspaceId,
                    'title' => 'Pending Tasks',
                    'widget_type' => 'pending_tasks',
                    'position' => 4,
                    'active' => true,
                ],
            ]);

            $widgets = DashboardWidget::where(
                'workspace_id',
                $workspaceId
            )
                ->where('active', true)
                ->orderBy('position')
                ->get();
        }

        return Inertia::render('Dashboard', [

            'widgets' => $widgets,

            'stats' => [

                'totalProjects' => Project::where(
                    'workspace_id',
                    $workspaceId
                )->count(),

                'teamMembers' => Member::where(
                    'workspace_id',
                    $workspaceId
                )->count(),

                'completedTasks' => Task::where(
                    'workspace_id',
                    $workspaceId
                )
                    ->where('status', 'Completed')
                    ->count(),

                'pendingTasks' => Task::where(
                    'workspace_id',
                    $workspaceId
                )
                    ->where('status', '!=', 'Completed')
                    ->count(),
            ],

            'projects' => Project::where(
                'workspace_id',
                $workspaceId
            )
                ->select(
                    'id',
                    'name',
                    'progress',
                    'status',
                    'deadline'
                )
                ->orderBy('created_at', 'desc')
                ->get(),

            'members' => Member::where(
                'workspace_id',
                $workspaceId
            )
                ->select(
                    'id',
                    'first_name',
                    'last_name',
                    'role'
                )
                ->orderBy('first_name')
                ->get()

        ]);
    }
}