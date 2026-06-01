<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;

use Inertia\Inertia;

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
        $leaders = Member::where('role', 'TL')
            ->with('teamMembers')
            ->get();

        $members = Member::where('role', 'Member')
            ->whereNull('assigned_to')
            ->get();

        return Inertia::render('Members', [
            'teamLeaders' => $leaders,
            'members' => $members,
        ]);
    }

    public function assignMember(Request $request, Member $member)
    {
        $member->update([
            'assigned_to' => $request->assigned_to,
        ]);

        return back();
    }
}
