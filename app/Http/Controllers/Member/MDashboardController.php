<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;

class MDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Member/Dashboard', [
            'member' => [
                'first_name' => 'Alex',
                'last_name' => 'Mercer',
                'email' => 'alex.m@workspace.io'
            ],
            'stats' => [
                'total_projects' => 3,
                'total_tasks' => 14,
                'completed_tasks' => 8,
                'pending_tasks' => 6
            ],
            'performanceData' => [
                'completed_pct' => 57,
                'in_progress_pct' => 28,
                'assigned_pct' => 15,
                'productivity_score' => 94
            ],
            'tasks' => [
                [
                    'id' => 1,
                    'title' => 'Optimize database indexes for workspace tracking schemas',
                    'project_name' => 'Phoenix Core Engine',
                    'priority' => 'High',
                    'deadline' => 'June 22, 2026',
                    'status' => 'In Progress'
                ],
                [
                    'id' => 2,
                    'title' => 'Wireframe modular custom widget drag-and-drop system UI',
                    'project_name' => 'UI Kit Architecture',
                    'priority' => 'Medium',
                    'deadline' => 'June 26, 2026',
                    'status' => 'Todo'
                ],
                [
                    'id' => 3,
                    'title' => 'Implement JWT authorization guards on micro-endpoints',
                    'project_name' => 'Gateway Middleware',
                    'priority' => 'High',
                    'deadline' => 'June 19, 2026',
                    'status' => 'Completed'
                ]
            ],
            'projects' => [
                ['id' => 101, 'name' => 'Phoenix Core Engine', 'total_tasks' => 8, 'completed_tasks' => 5, 'deadline' => 'July 15, 2026'],
                ['id' => 102, 'name' => 'UI Kit Architecture', 'total_tasks' => 4, 'completed_tasks' => 1, 'deadline' => 'Aug 02, 2026'],
                ['id' => 103, 'name' => 'Gateway Middleware', 'total_tasks' => 2, 'completed_tasks' => 2, 'deadline' => 'June 20, 2026']
            ],
            'notifications' => [
                [
                    'id' => 1,
                    'title' => 'Code Review Request',
                    'message' => 'Leader tagged you on pull request #44',
                    'is_read' => false
                ]
            ]
        ]);
    }
}