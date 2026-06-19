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
        ]);

        Member::create([
            'workspace_id' => session('workspace_id'),

            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'department' => $validated['department'],
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Member created successfully');
    }

    public function index()
    {
        $workspaceId = session('workspace_id');

        $leaders = Member::where('workspace_id', $workspaceId)
            ->where('role', 'TL')
            ->with('teamMembers')
            ->get();

        $members = Member::where('workspace_id', $workspaceId)
            ->where('role', 'Member')
            ->whereNull('assigned_to')
            ->get();

        $orphanMembers = Member::whereNull('workspace_id')
            ->get();

        $workspaces = Workspace::all();

        return Inertia::render('Members', [
            'teamLeaders' => $leaders,
            'members' => $members,
            'orphanMembers' => $orphanMembers,
            'workspaces' => $workspaces,
            'currentWorkspace' => session('workspace_id'),
        ]);
    }

    public function assignMember(Request $request, Member $member)
    {
        $leader = Member::findOrFail(
            $request->assigned_to
        );

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
}
