<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Project;
use App\Models\Task;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
dd('MEMBER DASHBOARD HIT');
class MemberDashboardController extends Controller
{
    public function index()
    {
        $authUser = Auth::user();

        $member = Member::where('email', $authUser->email)->first();

        if (!$member) {
            abort(403);
        }

        $team = $member->team_id
            ? Member::where('team_id', $member->team_id)->get()
            : collect();

        $teamLeader = Member::where('id', $member->assigned_to)->first();

        $tasks = Task::with('project')
            ->whereJsonContains('member_id', $member->id)
            ->orderByRaw("FIELD(status,'In Progress','Review','Todo','Completed')")
            ->orderBy('due_date')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'priority' => $task->priority ?? 'Medium',
                    'deadline' => optional($task->due_date)->toDateString(),
                    'project_id' => $task->project_id,
                    'project_name' => $task->project->name ?? 'Unknown',
                ];
            });

        $projects = Project::with(['tasks'])
            ->whereHas('tasks', function ($q) use ($member) {
                $q->whereJsonContains('member_id', $member->id);
            })
            ->orderBy('name')
            ->get();

        foreach ($projects as $project) {
            $projectTasks = $project->tasks->filter(function ($task) use ($member) {
                return in_array($member->id, $task->member_id ?? []);
            });

            $total = $projectTasks->count();
            $completed = $projectTasks->where('status', 'Completed')->count();

            $project->total_tasks = $total;
            $project->completed_tasks = $completed;
            $project->progress = $total ? round(($completed / $total) * 100) : 0;
        }

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'Completed')->count();
        $pendingTasks = $tasks->where('status', '!=', 'Completed')->count();

        $overdueTasks = $tasks->filter(function ($task) {
            return $task['status'] !== 'Completed'
                && $task['deadline']
                && now()->gt($task['deadline']);
        })->count();

        $stats = [
            'projects' => $projects->count(),
            'tasks' => $totalTasks,
            'completed' => $completedTasks,
            'pending' => $pendingTasks,
            'overdue' => $overdueTasks,
            'completion_rate' => $totalTasks ? round(($completedTasks / $totalTasks) * 100) : 0,
        ];

        $projectTotal = $projects->sum('total_tasks');
        $projectDone = $projects->sum('completed_tasks');

        $performanceData = [
            'productivity_score' => $stats['completion_rate'],
            'project_completion_rate' => $projectTotal ? round(($projectDone / $projectTotal) * 100) : 0,
        ];

        $notifications = Notification::where('user_id', $authUser->id)
            ->orderBy('is_read')
            ->orderByDesc('created_at')
            ->limit(25)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'is_read' => (bool) $n->is_read,
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Member/Dashboard', [
            'member' => $member,
            'team' => [
                'id' => $member->team_id,
                'members' => $team,
            ],
            'teamLeader' => $teamLeader,
            'tasks' => $tasks,
            'projects' => $projects,
            'stats' => $stats,
            'performanceData' => $performanceData,
            'notifications' => $notifications,
        ]);
    }

    public function toggleTask(Task $task)
    {
        $member = Member::where('email', Auth::user()->email)->first();

        abort_unless(in_array($member->id, $task->member_id ?? []), 403);

        $task->status = $task->status === 'Completed' ? 'In Progress' : 'Completed';
        $task->save();

        return back();
    }

    public function markNotificationRead(Notification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        $notification->update(['is_read' => true]);

        return back();
    }

    public function markAllNotificationsRead()
    {
        Notification::where('user_id', Auth::id())
            ->update(['is_read' => true]);

        return back();
    }
}