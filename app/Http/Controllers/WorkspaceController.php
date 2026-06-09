<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:100',
            'description' => 'nullable|max:500',
        ]);

        $workspace = Workspace::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        session(['workspace_id' => $workspace->id]);

        return response()->json($workspace);
    }

    public function select(Request $request)
    {
        session(['workspace_id' => $request->workspace_id]);
        session()->forget('show_workspace_modal');

        return back();
    }

    public function index()
    {
        return response()->json(Workspace::all());
    }

    public function show(Workspace $workspace)
    {
        return response()->json($workspace);
    }

    public function update(Request $request, Workspace $workspace)
    {
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
        $workspace->delete();

        if (session('workspace_id') == $workspace->id) {
            session()->forget('workspace_id');
        }

        return response()->json([
            'message' => 'Workspace deleted successfully'
        ]);
    }
}