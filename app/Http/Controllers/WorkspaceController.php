<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Member;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\DashboardWidget;
use App\Models\Invitation;
use App\Models\TeamRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkspaceController extends Controller
{
    private function authorizeWorkspace(Workspace $workspace): void
    {
        if ($workspace->owner_id != auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:100',
            'description' => 'nullable|max:500',
        ]);

        $workspace = Workspace::create([
            'owner_id'    => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
        ]);

        session(['workspace_id' => $workspace->id]);

        return back()->with('success', 'Workspace created successfully');
    }

    public function select(Request $request)
    {
        $workspace = Workspace::where('id', $request->workspace_id)
            ->where('owner_id', auth()->id())
            ->first();

        if (!$workspace) {
            return back()->with('error', 'Workspace not found or access denied.');
        }

        session(['workspace_id' => $workspace->id]);
        session()->forget('show_workspace_modal');

        return back();
    }

    public function index()
    {
        return response()->json(Workspace::all());
    }

    public function show(Workspace $workspace)
    {
        $this->authorizeWorkspace($workspace);

        return response()->json($workspace);
    }

    public function update(Request $request, Workspace $workspace)
    {
        $this->authorizeWorkspace($workspace);

        $request->validate([
            'name' => 'required|min:3|max:100',
            'description' => 'nullable|max:500',
        ]);

        $workspace->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Workspace updated successfully',
            'workspace' => $workspace
        ]);
    }

    public function destroy(Workspace $workspace)
    {
        $this->authorizeWorkspace($workspace);

        DB::transaction(function () use ($workspace) {
            Member::where('workspace_id', $workspace->id)
                ->update([
                    'workspace_id' => null,
                    'assigned_to' => null,
                ]);

            User::where('workspace_id', $workspace->id)
                ->update([
                    'workspace_id' => null,
                    'role' => null,
                ]);

            Task::where('workspace_id', $workspace->id)->delete();

            Project::where('workspace_id', $workspace->id)->delete();

            DashboardWidget::where('workspace_id', $workspace->id)->delete();


            Invitation::where('workspace_id', $workspace->id)->delete();

            Notification::where('workspace_id', $workspace->id)->delete();

            TeamRequest::where('workspace_id', $workspace->id)->delete();

            if (session('workspace_id') == $workspace->id) {
                session()->forget('workspace_id');
            }

            $workspace->delete();
        });

        return response()->json([
            'message' => 'Workspace deleted successfully'
        ]);
    }
}
