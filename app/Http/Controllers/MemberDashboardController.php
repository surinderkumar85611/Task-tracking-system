<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class MemberDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        

        $team = $user->team ?? null;

        $teamLeader = $team
            ? User::where('id', $team->leader_id)->first()
            : null;


        $tasks = Task::where('member_id', $user->id)
            ->with('project')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'priority' => $task->priority ?? 'Medium',
                    'deadline' => $task->deadline,
                    'project_id' => $task->project_id,
                    'project_name' => $task->project->name ?? 'Unknown',
                ];
            });

    

        $projects = Project::whereHas('tasks', function ($q) use ($user) {
                $q->where('member_id', $user->id);
            })
            ->with(['tasks' => function ($q) use ($user) {
                $q->where('member_id', $user->id);
            }])
            ->get()
            ->map(function ($project) {
                $total = $project->tasks->count();
                $completed = $project->tasks->where('status', 'Completed')->count();

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'deadline' => $project->deadline,
                    'total_tasks' => $total,
                    'completed_tasks' => $completed,
                ];
            });

        
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'Completed')->count();

        $stats = [
            'total_projects' => $projects->count(),
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $totalTasks - $completedTasks,
        ];

    
        $performanceData = [
            'productivity_score' => $totalTasks > 0
                ? round(($completedTasks / $totalTasks) * 100)
                : 0,
        ];


        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();


        return Inertia::render('Member/Dashboard', [
            'member' => $user,
            'team' => $team,
            'teamLeader' => $teamLeader,
            'tasks' => $tasks,
            'projects' => $projects,
            'stats' => $stats,
            'performanceData' => $performanceData,
            'notifications' => $notifications,
        ]);
    }
}