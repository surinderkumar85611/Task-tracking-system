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

        session([
            'workspace_id' => $workspace->id
        ]);

        return back();
    }

    public function select(Request $request)
    {
        session([
            'workspace_id' => $request->workspace_id
        ]);

        session()->forget('show_workspace_modal');

        return back();
    }
}
