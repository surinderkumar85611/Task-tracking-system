<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Member;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:3|max:255',
            'status' => 'required',
            'deadline' => 'required|date',
            'description' => 'required|min:5',
            'team_leader_id' => 'required|exists:members,id',
        ]);

        Project::create([
            'workspace_id' => session('workspace_id'),
            'team_leader_id' => $validated['team_leader_id'],
            'name' => $validated['name'],
            'status' => $validated['status'],
            'deadline' => $validated['deadline'],
            'description' => $validated['description'],
            'progress' => 0,
        ]);

        return back()->with('success', 'Project created successfully');
    }

    public function index()
    {
        $projects = Project::where(
            'workspace_id',
            session('workspace_id')
        )->get();

        $teamLeaders = Member::where(
            'workspace_id',
            session('workspace_id')
        )
        ->where('role', 'TL')
        ->with('teamMembers')
        ->get();

        return Inertia::render('Projects', [
            'projects' => $projects,
            'teamLeaders' => $teamLeaders,
        ]);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return back()->with(
            'success',
            'Project deleted successfully'
        );
    }
}
