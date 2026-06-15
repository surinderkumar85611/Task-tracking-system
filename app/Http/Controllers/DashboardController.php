<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Member;
use App\Models\Task;
use App\Models\DashboardWidget;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $workspaceId = session('workspace_id');

        return Inertia::render('Dashboard', [

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

            'widgets' => DashboardWidget::where(
                'workspace_id',
                $workspaceId
            )
            ->orderBy('position')
            ->get()

        ]);
    }

    public function reorderWidgets(Request $request)
    {
        foreach ($request->widgets as $widget) {

            DashboardWidget::where(
                'id',
                $widget['id']
            )->update([
                'position' => $widget['position']
            ]);
        }

        return back();
    }
}
