<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'project_id'            => 'required',
            'title'                 => 'required',
            'member_id'             => 'nullable|array',
            'notes'                 => 'nullable|string',
            'allocated_duration'    => 'nullable|string',
            'timer_started_at'      => 'nullable|string',
            'review'                => 'nullable|string',
        ]);

        Task::create([
            'workspace_id'       => $request->workspace_id, 
            'project_id'           => $request->project_id,
            // 'member_id'            => !empty($request->member_id) ? $request->member_id : [],
            'member_id'            => $request->member_id ?: null,
            'title'                => $request->title,
            'description'          => $request->description,
            'priority'             => $request->priority ?? 'Medium',
            'status'               => $request->status ?? 'Todo',
            'due_date'             => $request->deadline,
            'allocated_duration'   => $request->allocated_duration,
            'timer_started_at'     => $request->timer_started_at,
            'notes'                => $request->notes ? [['sender' => 'Admin', 'text' => $request->notes, 'created_at' => now()->toIso8601String()]] : [],
            'review'               => $request->review,
        ]);

        return back();
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate([
            'project_id' => 'required',
            'title'      => 'required',
            'member_id'  => 'nullable',
            'notes'      => 'nullable|string',
        ]);

        $existingNotes = $task->notes ?? [];

        if ($request->has('notes') && !empty(trim($request->notes))) {
            $user = auth()->user();
            $senderName = 'Admin';

            if ($user) {
                $senderName = $user->first_name ?? $user->name ?? $user->username ?? 'Team Member';
            }

            $newComment = [
                'sender'     => $senderName,
                'text'       => trim($request->notes),
                'created_at' => now()->toIso8601String(),
            ];

            array_unshift($existingNotes, $newComment);
        }

        $task->update([
            'project_id'          => $request->project_id,
            'member_id'           => $request->member_id,
            'title'               => $request->title,
            'status'              => $request->status,
            'priority'            => $request->priority,
            'due_date'            => $request->deadline,
            'allocated_duration'  => $request->allocated_duration,
            'timer_started_at'    => $request->timer_started_at,
            'notes'               => $existingNotes,
            'review'              => $request->review,
        ]);

        return back();
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return back();
    }
}
