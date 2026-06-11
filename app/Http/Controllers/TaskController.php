<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Models\Task;
use Carbon\Carbon;

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

                            $task['due_date'] =
                                Carbon::createFromFormat(
                                    'd-m-Y',
                                    $date
                                )->format('Y-m-d');
                        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {

                            $task['due_date'] =
                                Carbon::createFromFormat(
                                    'd/m/Y',
                                    $date
                                )->format('Y-m-d');
                        } else {

                            $task['due_date'] =
                                Carbon::parse($date)
                                ->format('Y-m-d');
                        }
                    } catch (\Exception $e) {

                        $task['due_date'] = null;
                    }
                }
            }

            if (!empty($task['allocated_duration'])) {

                preg_match('/\d+/', $task['allocated_duration'], $matches);

                $task['allocated_duration'] =
                    isset($matches[0])
                    ? (int)$matches[0]
                    : null;
            }

            Task::create([
                'workspace_id' => session('workspace_id'),
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
            'project_id'            => 'required',
            'title'                 => 'required',
            'member_id'             => 'nullable|array',
            'notes'                 => 'nullable|string',
            'allocated_duration'    => 'nullable|string',
            'timer_started_at'      => 'nullable|string',
            'review'                => 'nullable|string',
        ]);

        Task::create([
            'workspace_id'         => session('workspace_id'),
            'project_id'           => $request->project_id,
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
