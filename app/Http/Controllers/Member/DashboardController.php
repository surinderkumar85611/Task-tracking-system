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

            $project->tasks_count = $project->tasks->count();
            $project->members_count = $this->uniqueMemberCount($project->tasks);
        }

        $completedTasks = $tasks->where('status', 'Completed')->count();
        $pendingTasks = $tasks->where('status', '!=', 'Completed')->count();

        $teamMembers = Member::where('team_id', $member->team_id)->get();

        $projectHistory = Project::whereIn('id', $projectIds)
            ->where('status', 'Completed')
            ->latest()
            ->get();

        $taskHistory = $tasks->where('status', 'Completed')->values();

        // Notifications for the bell dropdown: hide ones this same user
        // triggered themselves (e.g. they changed their own task's status).
        // NOTE: created_by stores the AUTH USER id (see NotificationService::create,
        // which writes auth()->id()) — not the Member id — so we compare
        // against $user->id here, matching how user_id itself is queried below.
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

        // Recent Activity feed: a real event log built from notifications
        // (title/message/who/when), not a re-sorted task list. Unlike the
        // bell dropdown above, this intentionally INCLUDES self-triggered
        // events too, since it's meant to read as "what happened to your
        // tasks" rather than "things you need to be told about."
        $recentActivity = Notification::where('user_id', $user->id)
            ->when(session('workspace_id'), function ($query) {
                $query->where(function ($q) {
                    $q->where('workspace_id', session('workspace_id'))
                        ->orWhereNull('workspace_id');
                });
            })
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

    /**
     * Resolves a friendly display name for whoever triggered a notification.
     * created_by is a User id. For admins we use the User's own name fields;
     * for members we look up their Member record the same way the rest of
     * the app matches User <-> Member (by email), since Member records hold
     * the first_name/last_name people actually recognize.
     */
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

    /**
     * Counts distinct members assigned across a project's tasks.
     * task.member_id may be stored as a JSON array, a decoded array,
     * or a single scalar id, same as the filtering logic above.
     */
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