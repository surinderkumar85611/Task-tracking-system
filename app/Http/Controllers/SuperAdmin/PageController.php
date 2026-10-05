<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Project;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Workspace;

class PageController extends Controller
{
    private function projectPayload($p): array
    {
        return [
            'id'         => $p->id,
            'name'       => $p->name,
            'progress'   => (int) $p->progress,
            'deadline'   => $p->deadline ? substr((string) $p->deadline, 0, 10) : null,
            'created_at' => optional($p->created_at)->toDateString(),
        ];
    }

    private function fullName($m): string
    {
        return trim($m->first_name . ' ' . $m->last_name);
    }

    public function teams()
    {
        $projectsByLeader = Project::all()->groupBy('team_leader_id');

        $leaders = Member::where('role', 'TL')
            ->with(['teamMembers', 'workspace'])
            ->orderBy('first_name')
            ->get()
            ->map(function ($tl) use ($projectsByLeader) {
                $projects  = $projectsByLeader->get($tl->id, collect());
                $total     = $projects->count();
                $completed = $projects->where('progress', '>=', 100)->count();

                return [
                    'id'              => $tl->id,
                    'name'            => $this->fullName($tl),
                    'email'           => $tl->email,
                    'level'           => $tl->level,
                    'workspace_name'  => $tl->workspace->name ?? 'No workspace',
                    'members'         => $tl->teamMembers->map(fn ($m) => [
                        'id'         => $m->id,
                        'name'       => $this->fullName($m),
                        'email'      => $m->email,
                        'department' => $m->department,
                        'role'       => $m->role,
                        'level'      => $m->level,
                    ])->values(),
                    'projects'        => $projects->map(fn ($p) => $this->projectPayload($p))->values(),
                    'completion_rate' => $total ? (int) round($completed / $total * 100) : 0,
                ];
            })->values();

        $unassigned = Member::whereNull('assigned_to')
            ->where('role', '!=', 'TL')
            ->orderBy('first_name')
            ->get()
            ->map(fn ($m) => [
                'id'         => $m->id,
                'name'       => $this->fullName($m),
                'email'      => $m->email,
                'department' => $m->department,
                'role'       => $m->role,
                'level'      => $m->level,
            ])->values();

        $admins = User::where('role', 'ADMIN')
            ->select('id', 'name', 'email', 'created_at')
            ->latest()
            ->get();

        $adminCount  = $admins->count();
        $leaderCount = $leaders->count();
        $memberCount = Member::where('role', '!=', 'TL')->count();

        return Inertia::render('SuperAdmin/Teams', [
            'admins'     => $admins,
            'leaders'    => $leaders,
            'unassigned' => $unassigned,
            'orgStats'   => [
                'admins'  => $adminCount,
                'leaders' => $leaderCount,
                'members' => $memberCount,
                'people'  => $adminCount + $leaderCount + $memberCount,
            ],
        ]);
    }

    public function projects()
    {
        $projectsByLeader = Project::all()->groupBy('team_leader_id');
        $workspaces       = Workspace::orderBy('name')->get();
        $today            = now()->toDateString();

        $allLeaders = Member::where('role', 'TL')
            ->with('teamMembers')
            ->orderBy('first_name')
            ->get();

        // one team leader with all of their projects
        $leaderPayload = function ($tl) use ($projectsByLeader, $workspaces, $today) {
            $projects = $projectsByLeader->get($tl->id, collect());
            $ongoing  = $projects->filter(fn ($p) => (int) $p->progress < 100);

            return [
                'id'              => $tl->id,
                'level'           => $tl->level,
                'name'            => $this->fullName($tl),
                'workspace_id'    => $tl->workspace_id,
                'workspace_name'  => optional($workspaces->firstWhere('id', $tl->workspace_id))->name ?? 'No workspace',
                'members_count'   => $tl->teamMembers->count(),
                'ongoing_count'   => $ongoing->count(),
                'completed_count' => $projects->count() - $ongoing->count(),
                'overdue_count'   => $ongoing->filter(
                    fn ($p) => $p->deadline && substr((string) $p->deadline, 0, 10) < $today
                )->count(),
                'needs_projects'  => $ongoing->count() === 0,
                'projects'        => $projects->sortBy('deadline')->map(fn ($p) => [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'progress'    => (int) $p->progress,
                    'assigned_at' => optional($p->created_at)->toDateString(),
                    'deadline'    => $p->deadline ? substr((string) $p->deadline, 0, 10) : null,
                ])->values(),
            ];
        };

        // administrators, each with the workspaces they own and the leaders inside them
        $admins = User::where('role', 'ADMIN')
            ->orderBy('name')
            ->get()
            ->map(function ($admin) use ($workspaces, $allLeaders, $leaderPayload) {
                $owned   = $workspaces->where('owner_id', $admin->id)->values();
                $leaders = $allLeaders
                    ->whereIn('workspace_id', $owned->pluck('id'))
                    ->map($leaderPayload)
                    ->values();

                return [
                    'id'                   => $admin->id,
                    'name'                 => $admin->name,
                    'email'                => $admin->email,
                    'workspaces'           => $owned->map(fn ($w) => ['id' => $w->id, 'name' => $w->name])->values(),
                    'leaders'              => $leaders,
                    'leaders_count'        => $leaders->count(),
                    'ongoing_count'        => $leaders->sum('ongoing_count'),
                    'needs_projects_count' => $leaders->where('needs_projects', true)->count(),
                ];
            })->values();

        // leaders whose workspace does not belong to any administrator
        $linkedWorkspaceIds = $workspaces->whereIn('owner_id', $admins->pluck('id'))->pluck('id');

        $unlinked = $allLeaders
            ->reject(fn ($tl) => $linkedWorkspaceIds->contains($tl->workspace_id))
            ->map($leaderPayload)
            ->values();

        // teams with no ongoing projects, they need work assigned
        $needs = collect();

        foreach ($admins as $a) {
            foreach ($a['leaders'] as $l) {
                if ($l['needs_projects']) {
                    $needs->push([
                        'id'              => $l['id'],
                        'name'            => $l['name'],
                        'workspace_name'  => $l['workspace_name'],
                        'admin_name'      => $a['name'],
                        'members_count'   => $l['members_count'],
                        'completed_count' => $l['completed_count'],
                    ]);
                }
            }
        }

        foreach ($unlinked as $l) {
            if ($l['needs_projects']) {
                $needs->push([
                    'id'              => $l['id'],
                    'name'            => $l['name'],
                    'workspace_name'  => $l['workspace_name'],
                    'admin_name'      => null,
                    'members_count'   => $l['members_count'],
                    'completed_count' => $l['completed_count'],
                ]);
            }
        }

        return Inertia::render('SuperAdmin/Projects', [
            'admins'        => $admins,
            'unlinked'      => $unlinked,
            'needsProjects' => $needs->values(),
            'stats'         => [
                'admins'         => $admins->count(),
                'leaders'        => $allLeaders->count(),
                'ongoing'        => Project::where('progress', '<', 100)->count(),
                'needs_projects' => $needs->count(),
            ],
        ]);
    }
}