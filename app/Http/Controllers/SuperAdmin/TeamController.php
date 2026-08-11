<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Member;

class TeamController extends Controller
{
    public function addMember(Member $teamLeader, Member $member)
    {
        if ($teamLeader->role !== 'TL') {
            return back()->withErrors(['member' => 'Selected leader is not a Team Leader.']);
        }

        $member->update([
            'assigned_to' => $teamLeader->id,
            'workspace_id' => $teamLeader->workspace_id,
        ]);

        return back()->with('success', 'Member added to team.');
    }

    public function removeMember(Member $teamLeader, Member $member)
    {
        if ($member->assigned_to === $teamLeader->id) {
            $member->update(['assigned_to' => null]);
        }

        return back()->with('success', 'Member removed from team.');
    }
}