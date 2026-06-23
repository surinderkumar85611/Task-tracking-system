<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Workspace;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([

            'firstName' => [
                'required',
                'min:2',
                'max:50',
                'regex:/^[A-Za-z]+$/'
            ],

            'lastName' => [
                'required',
                'min:2',
                'max:50',
                'regex:/^[A-Za-z]+$/'
            ],

            'email' => [
                'required',
                'email',
                'unique:members,email'
            ],

            'phone' => [
                'required',
                'regex:/^[0-9]+$/'
            ],

            'department' => [
                'required',
                'string',
                'max:100'
            ],

            'role' => [
                'required',
                'in:TL,Member'
            ],

            'level' => [
                'nullable',
                'required_if:role,TL',
                'in:1,2,3'
            ],
        ]);

        Member::create([
            'workspace_id' => session('workspace_id'),

            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'department' => $validated['department'],
            'role' => $validated['role'],
            'level' => $validated['level']
        ]);

        return back()->with('success', 'Member created successfully');
    }

    public function index()
    {
        $workspaceId = session('workspace_id');

        $leaders = Member::where('workspace_id', $workspaceId)
            ->where('role', 'TL')
            ->whereNull('assigned_to')
            ->with([
                'teamMembers.teamMembers',
                'teamMembers.teamMembers.teamMembers'
            ])
            ->get();

        $members = Member::where('workspace_id', $workspaceId)
            ->where('role', 'Member')
            ->whereNull('assigned_to')
            ->get();

        $emptyMembers = Member::whereNull('workspace_id')
            ->get();

        $workspaces = Workspace::all();

        return Inertia::render('Members', [
            'teamLeaders' => $leaders,
            'members' => $members,
            'emptyMembers' => $emptyMembers,
            'workspaces' => $workspaces,
            'currentWorkspace' => session('workspace_id'),
        ]);
    }

    public function assignMember(Request $request, Member $member)
    {
        $leader = Member::findOrFail($request->assigned_to);

        if ($member->role === 'TL') {

            if ($member->level == 3) {
                return back()->withErrors([
                    'hierarchy' => 'Senior TL cannot be moved'
                ]);
            }

            if ($leader->role !== 'TL') {
                return back()->withErrors([
                    'hierarchy' => 'TL can only be assigned to another TL'
                ]);
            }

            $allowed = [
                1 => [2, 3],
                2 => [3],
            ];

            if (
                !isset($allowed[$member->level]) ||
                !in_array($leader->level, $allowed[$member->level])
            ) {
                return back()->withErrors([
                    'hierarchy' => 'Invalid hierarchy'
                ]);
            }
        }

        $member->update([
            'assigned_to' => $leader->id,
            'workspace_id' => $leader->workspace_id,
        ]);

        return back();
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
        ]);

        $member->update($validated);

        return back()->with('success', 'Member updated successfully');
    }

    public function me()
    {
        $workspaceId = session('workspace_id');

        $member = Member::where('workspace_id', $workspaceId)
            ->where('email', Auth::user()->email)
            ->first();

        if (!$member) {
            return response()->json([
                'role' => '',
                'department' => ''
            ]);
        }

        return response()->json([
            'role' => $member->role,
            'department' => $member->department
        ]);
    }

    public function assignWorkspace(
        Request $request,
        Member $member
    ) {
        if ($member->role !== 'TL') {

            return response()->json([
                'message' => 'Only Team Leaders can be assigned'
            ], 422);
        }

        $member->update([
            'workspace_id' => $request->workspace_id,
            'assigned_to' => null,
        ]);

        return back()->with(
            'success',
            'Team Leader assigned successfully'
        );
    }

    public function makeIndependent(Member $member)
    {
        if (
            $member->role === 'TL'
            &&
            $member->level == 3
        ) {
            return back();
        }

        $member->update([
            'assigned_to' => null
        ]);

        return back();
    }
}
