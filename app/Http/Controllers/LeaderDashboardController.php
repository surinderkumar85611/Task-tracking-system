<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LeaderDashboardController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'teams' => 4,
            'projects' => 6,
            'tasks' => 18,
            'pendingTasks' => 5,
        ]);
    }

    public function teams()
    {
        return response()->json([
            [
                'id' => 1,
                'name' => 'Frontend Team',
                'members' => 5,
                'status' => 'active'
            ],
            [
                'id' => 2,
                'name' => 'Backend Team',
                'members' => 4,
                'status' => 'active'
            ],
            [
                'id' => 3,
                'name' => 'Design Team',
                'members' => 3,
                'status' => 'active'
            ]
        ]);
    }

    public function projects()
    {
        return response()->json([
            [
                'id' => 1,
                'name' => 'CRM Dashboard',
                'progress' => 75
            ],
            [
                'id' => 2,
                'name' => 'Mobile App',
                'progress' => 40
            ],
            [
                'id' => 3,
                'name' => 'Marketing System',
                'progress' => 90
            ]
        ]);
    }

    public function tasks()
    {
        return response()->json([
            [
                'id' => 1,
                'title' => 'Build Login Page',
                'assignee' => 'Sarah',
                'status' => 'in_progress'
            ],
            [
                'id' => 2,
                'title' => 'API Integration',
                'assignee' => 'David',
                'status' => 'pending'
            ],
            [
                'id' => 3,
                'title' => 'UI Fixes',
                'assignee' => 'Emma',
                'status' => 'completed'
            ]
        ]);
    }
}