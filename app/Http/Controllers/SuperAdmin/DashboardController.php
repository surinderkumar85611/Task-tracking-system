<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Project;
use App\Models\Member;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('SuperAdmin/Dashboard', [

            'stats' => [

                'admins' => User::where('role', 'ADMIN')->count(),

                'workspaces' => Workspace::count(),

                'projects' => Project::count(),

                'members' => Member::count(),

            ],

            'admins' => User::where('role', 'ADMIN')
                ->select('id', 'name', 'email', 'created_at')
                ->latest()
                ->get(),

        ]);
    }
}
