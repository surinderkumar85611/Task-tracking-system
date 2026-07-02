<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\Member;
use Inertia\Inertia;
use App\Services\NotificationService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProjectExport;

class ProjectController extends Controller
{
    private function authorizeProject(Project $project): void
    {
        if ($project->workspace_id != session('workspace_id')) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:3|max:255',
            'status' => 'required',
            'deadline' => 'required|date',
            'description' => 'required|min:5',
            'team_leader_id' => 'required|exists:members,id',
        ]);

        $project = Project::create([
            'workspace_id' => session('workspace_id'),
            'team_leader_id' => $validated['team_leader_id'],
            'name' => $validated['name'],
            'status' => $validated['status'],
            'deadline' => $validated['deadline'],
            'description' => $validated['description'],
            'progress' => 0,
        ]);

        $leader = Member::find($validated['team_leader_id']);

        if ($leader && !empty($leader->email)) {
            $targetUser = \App\Models\User::where('email', $leader->email)->first();

            if ($targetUser) {
                NotificationService::create(
                    $targetUser->id,
                    session('workspace_id'),
                    'project_assigned',
                    'New Project Assigned',
                    'You have been assigned a new project: ' . $project->name,
                    ['project_id' => $project->id]
                );
            }
        }

        return back()->with('success', 'Project created successfully');
    }

    public function index()
    {
        $projects = Project::with([
            'teamLeader.teamMembers',
            'tasks' => function ($query) {
                $query
                    ->orderByRaw("
                CASE
                    WHEN allocated_duration IS NULL
                    OR allocated_duration = ''
                    OR allocated_duration = 0
                    THEN 1
                    ELSE 0
                END
            ")
                    ->orderBy('allocated_duration', 'asc');
            }
        ])
            ->where('workspace_id', session('workspace_id'))
            ->get()
            ->map(function ($project) {
                $project->tasks->each(function ($task) {
                    $task->member = $task->assigned_members;
                });
                return $project;
            });

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

    public function update(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        $oldStatus = $project->status;

        $project->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'deadline' => $request->deadline,
        ]);

        if ($oldStatus !== $request->status) {
            $leader = Member::find($project->team_leader_id);

            if ($leader && !empty($leader->email)) {
                $targetUser = \App\Models\User::where('email', $leader->email)->first();

                if ($targetUser) {
                    NotificationService::create(
                        $targetUser->id,
                        session('workspace_id'),
                        'project_status_updated',
                        'Project Status Changed',
                        'Project status changed to ' . $request->status,
                        ['project_id' => $project->id]
                    );
                }
            }
        }

        return back();
    }

    public function destroy(Project $project)
    {
        $this->authorizeProject($project);

        $project->delete();

        return back()->with(
            'success',
            'Project deleted successfully'
        );
    }

    public function export(Project $project)
    {
        $this->authorizeProject($project);

        return Excel::download(
            new ProjectExport($project),
            $project->name . '.xlsx'
        );
    }
}
