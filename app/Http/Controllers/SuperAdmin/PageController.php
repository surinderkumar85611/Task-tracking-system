<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Project;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Workspace;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Task;
use App\Models\DashboardWidget;
use App\Models\Invitation;
use App\Models\TeamRequest;
use App\Models\Notification;

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
                    'workspace_id'    => $tl->workspace_id,
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

        // workspaces: each one with its administrator (owner) and its team leaders
        $workspaces = Workspace::orderBy('name')->get();
        $adminsById = $admins->keyBy('id');

        $groups = $workspaces->map(function ($w) use ($leaders, $adminsById) {
            $admin = $adminsById->get($w->owner_id);

            return [
                'id'         => $w->id,
                'name'       => $w->name,
                'admin'      => $admin ? [
                    'id'    => $admin->id,
                    'name'  => $admin->name,
                    'email' => $admin->email,
                ] : null,
                'leader_ids' => $leaders->where('workspace_id', $w->id)->pluck('id')->values(),
            ];
        })->values();

        // leaders that do not belong to any existing workspace
        $workspaceIds = $workspaces->pluck('id');
        $orphans      = $leaders->reject(fn ($l) => $workspaceIds->contains($l['workspace_id']))->pluck('id')->values();

        if ($orphans->count()) {
            $groups->push([
                'id'         => null,
                'name'       => 'No workspace',
                'admin'      => null,
                'leader_ids' => $orphans,
            ]);
        }

        return Inertia::render('SuperAdmin/Teams', [
            'admins'     => $admins,
            'leaders'    => $leaders,
            'unassigned' => $unassigned,
            'workspaces' => $groups,
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

    /* ------------------------------------------------------------------ */
    /* Workspaces: stats, administrator, team leaders                      */
    /* ------------------------------------------------------------------ */
    public function workspaces()
    {
        $workspaces = Workspace::orderBy('name')->get();
        $projects   = Project::all()->groupBy('workspace_id');
        $members    = Member::all()->groupBy('workspace_id');
        $owners     = User::whereIn('id', $workspaces->pluck('owner_id')->filter()->values())
            ->get()
            ->keyBy('id');
        $today      = now()->toDateString();

        $list = $workspaces->map(function ($w) use ($projects, $members, $owners, $today) {
            $ps = $projects->get($w->id, collect());
            $ms = $members->get($w->id, collect());

            $psByLeader = $ps->groupBy('team_leader_id');
            $owner      = $owners->get($w->owner_id);

            $total      = $ps->count();
            $completed  = $ps->where('progress', '>=', 100)->count();
            $inProgress = $ps->where('progress', '>', 0)->where('progress', '<', 100)->count();
            $pending    = $ps->where('progress', '<=', 0)->count();
            $overdue    = $ps->filter(
                fn ($p) => (int) $p->progress < 100 && $p->deadline && substr((string) $p->deadline, 0, 10) < $today
            )->count();

            return [
                'id'              => $w->id,
                'name'            => $w->name,
                'description'     => $w->description,
                'created_at'      => optional($w->created_at)->toDateString(),
                'admin'           => $owner ? [
                    'id'    => $owner->id,
                    'name'  => $owner->name,
                    'email' => $owner->email,
                ] : null,
                'leaders'         => $ms->where('role', 'TL')->map(fn ($tl) => [
                    'id'             => $tl->id,
                    'name'           => $this->fullName($tl),
                    'level'          => $tl->level,
                    'members_count'  => $ms->where('assigned_to', $tl->id)->count(),
                    'projects_count' => $psByLeader->get($tl->id, collect())->count(),
                ])->values(),
                'members_count'   => $ms->where('role', '!=', 'TL')->count(),
                'projects_total'  => $total,
                'completed'       => $completed,
                'in_progress'     => $inProgress,
                'pending'         => $pending,
                'overdue'         => $overdue,
                'completion_rate' => $total ? (int) round($completed / $total * 100) : 0,
            ];
        })->values();

        // team members who can be promoted to administrator (no team leaders, no administrators)
        $loginsByEmail = User::all(['id', 'email', 'role'])
            ->keyBy(fn ($u) => strtolower((string) $u->email));

        $candidates = Member::where('role', '!=', 'TL')
            ->orderBy('first_name')
            ->get()
            ->map(function ($m) use ($workspaces, $loginsByEmail) {
                $login = $loginsByEmail->get(strtolower((string) $m->email));

                return [
                    'id'             => $m->id,
                    'name'           => $this->fullName($m),
                    'email'          => $m->email,
                    'workspace_name' => optional($workspaces->firstWhere('id', $m->workspace_id))->name ?? 'No workspace',
                    'has_login'      => (bool) $login,
                    'is_admin'       => $login && strtoupper((string) $login->role) === 'ADMIN',
                ];
            })
            ->reject(fn ($m) => $m['is_admin'])
            ->values();

        return Inertia::render('SuperAdmin/Workspaces', [
            'candidates' => $candidates,
            'workspaces' => $list,
            'admins'     => User::where('role', 'ADMIN')->orderBy('name')->get(['id', 'name', 'email']),
            'stats'      => [
                'workspaces' => $list->count(),
                'leaders'    => $list->sum(fn ($w) => count($w['leaders'])),
                'members'    => $list->sum('members_count'),
                'projects'   => $list->sum('projects_total'),
            ],
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Work out the owner: an existing administrator, or a team member     */
    /* who is promoted to administrator                                    */
    /* ------------------------------------------------------------------ */
    private function resolveOwner($ownerId, $promoteMemberId)
    {
        if ($promoteMemberId) {
            $member = Member::find($promoteMemberId);

            if (!$member || $member->role === 'TL') {
                throw ValidationException::withMessages([
                    'owner' => 'Team leaders cannot be promoted. Pick a team member or an existing administrator.',
                ]);
            }

            $login = User::where('email', $member->email)->first();

            if (!$login) {
                throw ValidationException::withMessages([
                    'owner' => $this->fullName($member) . ' has no login account yet, so they cannot be made an administrator.',
                ]);
            }

            if (strtoupper((string) $login->role) === 'ADMIN') {
                throw ValidationException::withMessages([
                    'owner' => 'That person is already an administrator.',
                ]);
            }

            // administrator pages are blocked for anyone who still has a member record,
            // so the person leaves the team and becomes an administrator
            Member::where('assigned_to', $member->id)->update(['assigned_to' => null]);

            $login->role = 'ADMIN';
            $login->workspace_id = null;
            $login->save();

            $member->delete();

            return $login->id;
        }

        return $ownerId ? (int) $ownerId : null;
    }

    /* ------------------------------------------------------------------ */
    /* Add a workspace (an administrator must be picked as the owner)      */
    /* ------------------------------------------------------------------ */
    public function storeWorkspace(Request $request)
    {
        $data = $request->validate([
            'owner_id'          => ['nullable', Rule::exists('users', 'id')->where('role', 'ADMIN')],
            'promote_member_id' => ['nullable', 'integer', 'exists:members,id'],
            'name'              => [
                'required', 'string', 'min:3', 'max:100',
                Rule::unique('workspaces', 'name')->where('owner_id', $request->owner_id),
            ],
            'description'       => ['nullable', 'string', 'max:500'],
        ], [
            'name.unique'     => 'This administrator already has a workspace with that name.',
            'owner_id.exists' => 'Please pick a valid administrator.',
        ]);

        if (empty($data['owner_id']) && empty($data['promote_member_id'])) {
            throw ValidationException::withMessages(['owner' => 'Please pick an administrator.']);
        }

        DB::transaction(function () use ($data) {
            $ownerId = $this->resolveOwner($data['owner_id'] ?? null, $data['promote_member_id'] ?? null);

            Workspace::create([
                'owner_id'    => $ownerId,
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
            ]);
        });

        return back()->with('success', 'Workspace created.');
    }

    /* ------------------------------------------------------------------ */
    /* Edit a workspace: name, description and administrator               */
    /* ------------------------------------------------------------------ */
    public function updateWorkspace(Request $request, Workspace $workspace)
    {
        $data = $request->validate([
            'owner_id'          => ['nullable', Rule::exists('users', 'id')->where('role', 'ADMIN')],
            'promote_member_id' => ['nullable', 'integer', 'exists:members,id'],
            'name'              => [
                'required', 'string', 'min:3', 'max:100',
                Rule::unique('workspaces', 'name')
                    ->where('owner_id', $workspace->owner_id)
                    ->ignore($workspace->id),
            ],
            'description'       => ['nullable', 'string', 'max:500'],
        ], [
            'name.unique'     => 'This administrator already has a workspace with that name.',
            'owner_id.exists' => 'Please pick a valid administrator.',
        ]);

        DB::transaction(function () use ($data, $workspace) {
            $ownerId = $this->resolveOwner($data['owner_id'] ?? null, $data['promote_member_id'] ?? null);

            $workspace->update([
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'owner_id'    => $ownerId ?? $workspace->owner_id,
            ]);
        });

        return back()->with('success', 'Workspace updated.');
    }

    /* ------------------------------------------------------------------ */
    /* Delete a workspace: projects and tasks are deleted, people are      */
    /* unassigned. The workspace name must be typed to confirm.            */
    /* ------------------------------------------------------------------ */
    public function destroyWorkspace(Request $request, Workspace $workspace)
    {
        if (trim((string) $request->input('confirm_name')) !== $workspace->name) {
            return back()->withErrors(['workspace' => 'Type the exact workspace name to confirm.']);
        }

        try {
            DB::transaction(function () use ($workspace) {
                $projectIds = Project::where('workspace_id', $workspace->id)->pluck('id');

                // tasks first, then the projects they belong to
                Task::where(function ($q) use ($projectIds, $workspace) {
                    $q->whereIn('project_id', $projectIds)
                        ->orWhere('workspace_id', $workspace->id);
                })->delete();

                Project::where('workspace_id', $workspace->id)->delete();

                // team leaders and members stay in the system, but have no workspace or team
                Member::where('workspace_id', $workspace->id)
                    ->update(['workspace_id' => null, 'assigned_to' => null]);

                // same clean-up the administrator side does, but the owner keeps their role
                User::where('workspace_id', $workspace->id)
                    ->where('id', '!=', $workspace->owner_id)
                    ->update(['workspace_id' => null, 'role' => null]);

                User::where('id', $workspace->owner_id)
                    ->where('workspace_id', $workspace->id)
                    ->update(['workspace_id' => null]);

                DashboardWidget::where('workspace_id', $workspace->id)->delete();
                Invitation::where('workspace_id', $workspace->id)->delete();
                Notification::where('workspace_id', $workspace->id)->delete();
                TeamRequest::where('workspace_id', $workspace->id)->delete();

                $workspace->delete();
            });
        } catch (QueryException $e) {
            return back()->withErrors([
                'workspace' => 'This workspace is still linked to other records and could not be deleted.',
            ]);
        }

        return back()->with('success', 'Workspace deleted.');
    }

    /* ------------------------------------------------------------------ */
    /* Dashboard drag and drop: save a project's progress                  */
    /* ------------------------------------------------------------------ */
    public function updateProjectProgress(Request $request, Project $project)
    {
        $validated = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $project->update(['progress' => $validated['progress']]);

        return back()->with('success', 'Project progress updated.');
    }

    /* ------------------------------------------------------------------ */
    /* Remove an administrator                                             */
    /* ------------------------------------------------------------------ */
    public function destroyAdmin(User $user)
    {
        if (strtoupper((string) $user->role) !== 'ADMIN') {
            return back()->withErrors(['admin' => 'That user is not an administrator.']);
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            return back()->withErrors([
                'admin' => 'This administrator still owns data (for example workspaces) and cannot be removed yet.',
            ]);
        }

        return back()->with('success', 'Administrator removed.');
    }

    /* ------------------------------------------------------------------ */
    /* Remove a team leader (their members become unassigned)              */
    /* ------------------------------------------------------------------ */
    public function destroyLeader(Member $leader)
    {
        if ($leader->role !== 'TL') {
            return back()->withErrors(['leader' => 'That member is not a team leader.']);
        }

        if (Project::where('team_leader_id', $leader->id)->exists()) {
            return back()->withErrors([
                'leader' => 'This team leader still has projects. Reassign or delete those projects first.',
            ]);
        }

        try {
            DB::transaction(function () use ($leader) {
                Member::where('assigned_to', $leader->id)->update(['assigned_to' => null]);

                // remove the login account that belongs to this leader
                User::where('email', $leader->email)->delete();

                $leader->delete();
            });
        } catch (QueryException $e) {
            return back()->withErrors([
                'leader' => 'This team leader is still linked to other records and cannot be removed yet.',
            ]);
        }

        return back()->with('success', 'Team leader removed.');
    }
}