<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Task;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $member = Member::where('email', $user->email)->first();

        if (!$member) {
            abort(403);
        }

        $tasks = Task::with(['project.teamLeader'])
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
            })
            ->values();

        return Inertia::render('Member/Tasks', [
            'tasks' => $tasks
        ]);
    }
}