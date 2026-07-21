<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Project;
use App\Models\Notification;
use App\Models\Task;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $member = Member::where('email', $user->email)->first();

        if (!$member) {
            abort(403);
        }

        $tasks = Task::whereNotNull('id')
            ->when(session('workspace_id'), function ($query) {
                $query->where('workspace_id', session('workspace_id'));
            })
            ->get()
            ->filter(function ($task) use ($member) {

                if (is_array($task->member_id)) {
                    return in_array($member->id, $task->member_id);
                }

                $decoded = json_decode($task->member_id, true);

                if (is_array($decoded)) {
                    return in_array($member->id, $decoded);
                }

                return (int)$task->member_id === (int)$member->id;
            });

        $projectIds = $tasks->pluck('project_id')->unique();

        $projects = Project::with(['tasks'])
            ->whereIn('id', $projectIds)
            ->get();

        foreach ($projects as $project) {

            $projectTasks = $tasks->where('project_id', $project->id);

            $total = $projectTasks->count();

            $completed = $projectTasks->where('status', 'Completed')->count();

            $project->progress = $total > 0
                ? round(($completed / $total) * 100)
                : 0;
        }

        $completedTasks = $tasks->where('status', 'Completed')->count();
        $pendingTasks = $tasks->where('status', '!=', 'Completed')->count();

        $teamMembers = Member::where('team_id', $member->team_id)->get();

        $projectHistory = Project::whereIn('id', $projectIds)
            ->where('status', 'Completed')
            ->latest()
            ->get();

        $taskHistory = $tasks->where('status', 'Completed')->values();

        $notifications = Notification::where('user_id', $user->id)
            ->when(session('workspace_id'), function ($query) {
                $query->where(function ($q) {
                    $q->where('workspace_id', session('workspace_id'))
                        ->orWhereNull('workspace_id');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Member/Dashboard', [
            'member' => $member,
            'projects' => $projects,
            'tasks' => $tasks->values(),
            'teamMembers' => $teamMembers,
            'projectHistory' => $projectHistory,
            'taskHistory' => $taskHistory,
            'notifications' => $notifications,
            'stats' => [
                'assignedProjects' => $projects->count(),
                'assignedTasks' => $tasks->count(),
                'completedTasks' => $completedTasks,
                'pendingTasks' => $pendingTasks,
            ],
            'currentWorkspace' => session('workspace_id')
        ]);
    }
}