<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Project;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
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

        $member->avatar_url = $user->avatar_url;

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

                return (int) $task->member_id === (int) $member->id;
            });

        $projectIds = $tasks->pluck('project_id')->unique();

        $teamLeaderId = $member->assigned_to ?: $member->id;

        $projects = Project::with(['tasks'])
            ->when(session('workspace_id'), function ($query) {
                $query->where('workspace_id', session('workspace_id'));
            })
            ->where(function ($q) use ($projectIds, $teamLeaderId) {
                $q->whereIn('id', $projectIds)
                    ->orWhere('team_leader_id', $teamLeaderId);
            })
            ->get();

        foreach ($projects as $project) {

            $projectTasks = $tasks->where('project_id', $project->id);

            $total = $projectTasks->count();
            $completed = $projectTasks->where('status', 'Completed')->count();

            if ($total > 0) {
                $project->progress = round(($completed / $total) * 100);
            } else {
                $allTotal = $project->tasks->count();
                $allCompleted = $project->tasks->where('status', 'Completed')->count();

                $project->progress = $allTotal > 0
                    ? round(($allCompleted / $allTotal) * 100)
                    : ($project->progress ?? 0);
            }

            $project->tasks_count = $project->tasks->count();
            $project->members_count = $this->uniqueMemberCount($project->tasks);
        }

        $completedTasks = $tasks->where('status', 'Completed')->count();
        $pendingTasks = $tasks->where('status', '!=', 'Completed')->count();

        $teamMembers = Member::query()
            ->when(session('workspace_id'), function ($query) {
                $query->where('workspace_id', session('workspace_id'));
            })
            ->where(function ($q) use ($member) {
                $q->where('id', $member->id)
                    ->orWhere('assigned_to', $member->id);

                if ($member->assigned_to) {
                    $q->orWhere('id', $member->assigned_to)
                        ->orWhere('assigned_to', $member->assigned_to);
                }
            })
            ->get()
            ->each(function ($m) use ($member) {
                $m->is_leader = $member->assigned_to
                    ? (int) $m->id === (int) $member->assigned_to
                    : (int) $m->id === (int) $member->id;
            })
            ->sortByDesc('is_leader')
            ->values();

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
            ->where(function ($q) use ($user) {
                $q->whereNull('created_by')
                    ->orWhere('created_by', '!=', $user->id)
                    ->orWhere('created_by_role', '!=', 'member');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $recentActivity = Notification::where('user_id', $user->id)
            ->when(session('workspace_id'), function ($query) {
                $query->where(function ($q) {
                    $q->where('workspace_id', session('workspace_id'))
                        ->orWhereNull('workspace_id');
                });
            })
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notification) use ($user) {
                $data = is_array($notification->data)
                    ? $notification->data
                    : json_decode($notification->data ?? '', true);

                $taskId = $data['task_id'] ?? null;
                $task = $taskId ? Task::with('project')->find($taskId) : null;

                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'created_at' => $notification->created_at,
                    'task_title' => $task->title ?? null,
                    'project_name' => $task->project->name ?? null,
                    'actor_name' => $this->resolveActorName($notification),
                    'is_own' => $notification->created_by === $user->id,
                ];
            });

        return Inertia::render('Member/Dashboard', [
            'member' => $member,
            'teamProjects' => $projects,
            'myTasks' => $tasks->values(),
            'teamMembers' => $teamMembers,
            'projectHistory' => $projectHistory,
            'taskHistory' => $taskHistory,
            'notifications' => $notifications,
            'recentActivity' => $recentActivity,
            'stats' => [
                'teamProjects' => $projects->count(),
                'tasks' => $tasks->count(),
                'completed' => $completedTasks,
                'pending' => $pendingTasks,
            ],
            'currentWorkspaceId' => session('workspace_id'),
        ]);
    }

    private function resolveActorName(Notification $notification): string
    {
        if (!$notification->created_by) {
            return 'System';
        }

        $actorUser = User::find($notification->created_by);

        if (!$actorUser) {
            return 'System';
        }

        if (($notification->created_by_role ?? '') === 'admin') {
            return $actorUser->first_name
                ?? $actorUser->name
                ?? $actorUser->username
                ?? 'Admin';
        }

        $actorMember = Member::where('email', $actorUser->email)->first();

        return $actorMember->first_name
            ?? $actorUser->name
            ?? $actorUser->username
            ?? 'Team Member';
    }

    private function uniqueMemberCount($projectTasks): int
    {
        $ids = collect();

        foreach ($projectTasks as $task) {
            if (is_array($task->member_id)) {
                $ids = $ids->merge($task->member_id);
                continue;
            }

            $decoded = json_decode($task->member_id, true);

            if (is_array($decoded)) {
                $ids = $ids->merge($decoded);
                continue;
            }

            if (!is_null($task->member_id)) {
                $ids->push($task->member_id);
            }
        }

        return $ids->unique()->filter()->count();
    }
}
