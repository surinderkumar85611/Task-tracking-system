<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([

            'project_id' => 'required',

            'title' => 'required',

            'member_id' => 'required',
        ]);

        Task::create([

            'workspace_id' =>
                session('workspace_id'),

            'project_id' =>
                $request->project_id,

            'member_id' =>
                $request->member_id,

            'title' =>
                $request->title,

            'description' =>
                $request->description,

            'priority' =>
                $request->priority,

            'due_date' =>
                $request->deadline,
        ]);

        return back();
    }
}
