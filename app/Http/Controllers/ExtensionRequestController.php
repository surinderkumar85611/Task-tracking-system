<?php

namespace App\Http\Controllers;

use App\Models\ExtensionRequest;
use App\Models\Member;
use App\Models\Notification;
use App\Models\Project;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExtensionRequestController extends Controller
{
    // Must match the list in the admin Vue file
    private const REJECTION_REASONS = [
        'Taking too much time',
        'Progress not up to the mark',
        'Reason provided is not sufficient',
        'Deadline cannot be changed due to other commitments',
    ];

    // Case-insensitive so both "admin" and "ADMIN" work
    private function isAdmin($user): bool
    {
        return strtolower($user->role ?? '') === 'admin';
    }

    // Leader sends a request
    public function store(Request $request, Project $project)
    {
        // team_leader_id points to the members table, so match the leader by email
        $leaderMember = Member::find($project->team_leader_id);

        abort_unless(
            $leaderMember && $leaderMember->email === $request->user()->email,
            403
        );

        $data = $request->validate([
            'requested_deadline' => 'required|date|after_or_equal:today',
            'reason'             => 'nullable|string|max:1000',
        ]);

        if ($project->pendingExtensionRequest()->exists()) {
            return back()->withErrors([
                'requested_deadline' => 'A request is already pending for this project.',
            ]);
        }

        ExtensionRequest::create([
            'project_id'         => $project->id,
            'requested_by'       => $request->user()->id,
            'requested_deadline' => $data['requested_deadline'],
            'reason'             => $data['reason'] ?? null,
        ]);

        $name = trim($request->user()->first_name . ' ' . $request->user()->last_name)
            ?: ($request->user()->name ?? 'Team Leader');

        User::where('role', 'admin')->get()->each(function ($admin) use ($name, $project, $data) {
            NotificationService::create(
                $admin->id,
                $project->workspace_id ?? session('workspace_id'),
                'extension_requested',
                'Deadline Extension Requested',
                "{$name} requested a deadline extension for \"{$project->name}\" to {$data['requested_deadline']}.",
                ['project_id' => $project->id]
            );
        });

        return back();
    }

    // Admin approves
    public function approve(Request $request, ExtensionRequest $extensionRequest)
    {
        return $this->decide($request, $extensionRequest, 'approved');
    }

    // Admin rejects (a reason from the list is required)
    public function reject(Request $request, ExtensionRequest $extensionRequest)
    {
        abort_unless($this->isAdmin($request->user()), 403);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', Rule::in(self::REJECTION_REASONS)],
        ]);

        return $this->decide($request, $extensionRequest, 'rejected', $data['rejection_reason']);
    }

    private function decide(
        Request $request,
        ExtensionRequest $extensionRequest,
        string $status,
        ?string $rejectionReason = null
    ) {
        abort_unless($this->isAdmin($request->user()), 403);
        abort_unless($extensionRequest->status === 'pending', 422);

        $extensionRequest->update([
            'status'      => $status,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $project = $extensionRequest->project;

        if ($status === 'approved') {
            $project->update(['deadline' => $extensionRequest->requested_deadline]);
        }

        // Mark the admins' "requested" notifications for this project as read
        Notification::whereIn('user_id', User::where('role', 'admin')->pluck('id'))
            ->where('type', 'extension_requested')
            ->where('is_read', false)
            ->get()
            ->each(function ($n) use ($project) {
                if (($n->data['project_id'] ?? null) == $project->id) {
                    $n->update(['is_read' => true]);
                }
            });

        // Tell the leader what happened
        NotificationService::create(
            $extensionRequest->requested_by,
            $project->workspace_id ?? session('workspace_id'),
            $status === 'approved' ? 'extension_approved' : 'extension_rejected',
            $status === 'approved' ? 'Extension Approved' : 'Extension Rejected',
            $status === 'approved'
                ? "Your extension request for \"{$project->name}\" was approved."
                : "Your extension request for \"{$project->name}\" was rejected. Reason: {$rejectionReason}.",
            ['project_id' => $project->id]
        );

        return back();
    }

    // Mark one of the user's own notifications as read
    public function markRead(Request $request, $id)
    {
        Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->update(['is_read' => true]);

        return back();
    }
}