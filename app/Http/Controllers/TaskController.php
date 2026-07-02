<?php

namespace App\Support\Facades\Schema; // Keeping your top structural setup intact
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Models\Task;
use Carbon\Carbon;
use App\Services\NotificationService;
use App\Models\Member;
use App\Models\User;

class TaskController extends Controller
{
    public function getTaskFields()
    {
        $columns = Schema::getColumnListing('tasks');

        $exclude = [
            'id',
            'workspace_id',
            'project_id',
            'member_id',
            'review',
            'created_at',
            'updated_at',
        ];

        $fields = collect($columns)
            ->reject(fn($col) => in_array($col, $exclude))
            ->map(fn($col) => [
                'key' => $col,
                'label' => ucwords(str_replace('_', ' ', $col))
            ])
            ->values();

        return response()->json($fields);
    }

    public function import(Request $request)
    {
        foreach ($request->tasks as $task) {

            if (!empty($task['due_date'])) {

                if (is_numeric($task['due_date'])) {

                    $task['due_date'] = Carbon::createFromDate(1899, 12, 30)
                        ->addDays((int)$task['due_date'])
                        ->format('Y-m-d');
                } else {

                    try {

                        $date = trim($task['due_date']);

                        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                            $task['due_date'] = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
                        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
                            $task['due_date'] = Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
                        } else {
                            $task['due_date'] = Carbon::parse($date)->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        $task['due_date'] = null;
                    }
                }
            }

            if (!empty($task['allocated_duration'])) {
                preg_match('/\d+/', $task['allocated_duration'], $matches);
                $task['allocated_duration'] = isset($matches[0]) ? (int)$matches[0] : null;
            }

            Task::create([
                'workspace_id' => $request->workspace_id,
                'project_id' => $task['project_id'],
                'title' => $task['title'],
                'priority' => $task['priority'] ?? 'Medium',
                'status' => $task['status'] ?? 'Todo',
                'due_date' => $task['due_date'],
                'allocated_duration' => $task['allocated_duration'] ?? null,
            ]);
        }

        return back()->with('success', 'Tasks imported');
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'title' => 'required',
            'member_id' => 'nullable|array',
            'notes' => 'nullable|string',
            'allocated_duration' => 'nullable|string',
            'timer_started_at' => 'nullable|string',
            'review' => 'nullable|string',
            'workspace_id' => 'required',
        ]);

        $task = Task::create([
            'workspace_id' => $request->workspace_id,
            'project_id' => $request->project_id,
            'member_id' => is_array($request->member_id) ? array_values($request->member_id) : [],
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority ?? 'Medium',
            'status' => $request->status ?? 'Todo',
            'due_date' => $request->deadline ?? $request->due_date,
            'allocated_duration' => $request->allocated_duration,
            'timer_started_at' => $request->timer_started_at,
            'notes' => $request->notes ? [
                [
                    'sender' => 'Admin',
                    'text' => $request->notes,
                    'created_at' => now()->toIso8601String()
                ]
            ] : [],
            'review' => $request->review,
        ]);

        if (!empty($request->member_id)) {
            foreach ((array)$request->member_id as $memberId) {
                $member = Member::find($memberId);

                if ($member && !empty($member->email)) {
                    $targetUser = \App\Models\User::where('email', $member->email)->first();

                    if ($targetUser) {
                        NotificationService::create(
                            $targetUser->id,
                            $request->workspace_id ?? session('workspace_id'),
                            'task_assigned',
                            'New Task Assigned',
                            'You have been assigned a task: ' . $task->title,
                            ['task_id' => $task->id]
                        );
                    }
                }
            }
        }

        if (($request->priority ?? 'Medium') === 'High' && !empty($request->member_id)) {
            foreach ((array)$request->member_id as $memberId) {
                $member = Member::find($memberId);

                if ($member && !empty($member->email)) {
                    $targetUser = \App\Models\User::where('email', $member->email)->first();

                    if ($targetUser) {
                        NotificationService::create(
                            $targetUser->id,
                            $request->workspace_id ?? session('workspace_id'),
                            'task_urgent',
                            'Urgent Task Assigned',
                            'You have been assigned an URGENT task: ' . $task->title,
                            ['task_id' => $task->id]
                        );
                    }
                }
            }
        }

        $project = $task->project;

        if ($project && $project->team_leader_id) {

            $leader = Member::find($project->team_leader_id);

            if ($leader && $leader->email) {

                $leaderUser = User::where('email', $leader->email)->first();

                if ($leaderUser) {

                    NotificationService::create(
                        $leaderUser->id,
                        $request->workspace_id ?? session('workspace_id'),
                        'task_created',
                        'New Task Created',
                        'A new task has been created: ' . $task->title,
                        [
                            'task_id' => $task->id
                        ]
                    );
                }
            }
        }

        return back();
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate([
            'project_id' => 'required',
            'title' => 'required',
            'member_id' => 'nullable',
            'notes' => 'nullable|string',
            'is_read' => 'nullable|boolean',
        ]);

        $oldPriority = $task->priority;
        $oldStatus = $task->status;
        $existingNotes = $task->notes ?? [];

        if ($request->has('notes') && !empty(trim($request->notes))) {

            $user = auth()->user();

            $senderName =
                $user->first_name
                ?? $user->name
                ?? $user->username
                ?? 'Team Member';

            array_unshift($existingNotes, [
                'id' => time(),
                'sender' => $senderName,
                'text' => trim($request->notes),
                'reply_to' => $request->reply_to ?? null,
                'reactions' => [],
                'created_at' => now()->toIso8601String(),
            ]);

            $project = $task->project;


            if ($project) {
                if (auth()->user()->role === 'admin') {
                    if ($project->team_leader_id) {
                        $leader = Member::find($project->team_leader_id);
                        if ($leader) {
                            $leaderUser = User::where('email', $leader->email)->first();
                            if ($leaderUser)
                                NotificationService::create(
                                    $leaderUser->id,
                                    session('workspace_id'),
                                    'task_update',
                                    'New Task Update',
                                    $senderName . ' added an update on ' . $task->title,
                                    [
                                        'task_id' => $task->id
                                    ]
                                );
                        }
                    }
                }
            } else {

                $admins = \App\Models\User::where(
                    'role',
                    'admin'
                )->get();

                foreach ($admins as $admin) {
                    NotificationService::create(

                        $admin->id,

                        session('workspace_id'),

                        'task_update',

                        'New Task Update',

                        $senderName . ' added an update on ' . $task->title,

                        [
                            'task_id' => $task->id
                        ]

                    );
                }
            }
        }

        $task->update([
            'project_id' => $request->project_id,
            'member_id' => $request->member_id,
            'title' => $request->title,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->deadline,
            'allocated_duration' => $request->allocated_duration,
            'timer_started_at' => $request->timer_started_at,
            'notes' => $existingNotes,
            'review' => $request->review,
            'is_read' => $request->has('is_read') ? $request->is_read : $task->is_read,
        ]);

        if ($request->status && $oldStatus !== $request->status) {
            foreach ((array)$task->member_id as $memberId) {
                $member = Member::find($memberId);
                if ($member && !empty($member->email)) {
                    $targetUser = \App\Models\User::where('email', $member->email)->first();
                    if ($targetUser) {
                        NotificationService::create(
                            $targetUser->id,
                            session('workspace_id'),
                            'task_status_updated',
                            'Task Updated',
                            'Task status changed to: ' . $request->status,
                            ['task_id' => $task->id]
                        );
                    }
                }
            }
        }

        if ($request->priority === 'High' && $oldPriority !== 'High') {
            foreach ((array)$task->member_id as $memberId) {
                $member = Member::find($memberId);
                if ($member && !empty($member->email)) {
                    $targetUser = \App\Models\User::where('email', $member->email)->first();
                    if ($targetUser) {
                        NotificationService::create(
                            $targetUser->id,
                            session('workspace_id'),
                            'task_urgent',
                            'Task Became Urgent',
                            'Priority changed to HIGH: ' . $task->title,
                            ['task_id' => $task->id]
                        );
                    }
                }
            }
        }

        return back();
    }

    public function destroy($id)
    {
        $task = Task::with('project')->findOrFail($id);
        $task->delete();

        return back();
    }

    public function reply(Request $request, $id)
    {

        $task = Task::with('project')->findOrFail($id);

        $request->validate([
            'message' => 'required',
            'reply_to' => 'required'
        ]);


        $notes = $task->notes ?? [];


        $user = auth()->user();


        array_unshift($notes, [
            'id' => time(),
            'sender' => $user->name ?? 'User',
            'text' => $request->message,
            'reply_to' => $request->reply_to,
            'reactions' => [],
            'created_at' => now()->toIso8601String()
        ]);

        $task->update([
            'notes' => $notes
        ]);

        return response()->json([
            'success' => true,
            'notes' => $notes
        ]);
    }

    public function react(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $request->validate([
            'message_id' => 'required',
            'reaction' => 'required'
        ]);

        $notes = $task->notes ?? [];

        foreach ($notes as &$note) {

            if ($note['id'] == $request->message_id) {


                if (!isset($note['reactions'])) {
                    $note['reactions'] = [];
                }


                if (!isset($note['reactions'][$request->reaction])) {
                    $note['reactions'][$request->reaction] = [];
                }


                $existingIndex = collect(
                    $note['reactions'][$request->reaction]
                )->search(function ($item) {

                    return $item['user_id'] == auth()->id();
                });

                if ($existingIndex !== false) {

                    unset(
                        $note['reactions'][$request->reaction][$existingIndex]
                    );

                    $note['reactions'][$request->reaction] =
                        array_values(
                            $note['reactions'][$request->reaction]
                        );
                } else {

                    $note['reactions'][$request->reaction][] = [
                        'user_id' => auth()->id(),
                        'user' => auth()->user()->name
                    ];
                }
            }
        }

        $task->update([
            'notes' => $notes
        ]);

        return response()->json([
            'success' => true,
            'notes' => $notes
        ]);
    }
}
