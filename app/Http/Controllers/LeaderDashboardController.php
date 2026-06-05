<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class LeaderDashboardController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'teams' => Team::count(),
            'projects' => Project::count(),
            'tasks' => Task::count(),
            'pendingTasks' => Task::where('status', 'pending')->count(),
        ]);
    }

    public function teams()
    {
        return Team::all();
    }

    public function projects()
    {
        return Project::all();
    }

    public function tasks()
    {
        return Task::all();
    }
}