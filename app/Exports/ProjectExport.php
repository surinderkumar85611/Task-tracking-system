<?php

namespace App\Exports;

use App\Models\Project;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProjectExport implements FromView
{
    protected Project $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function view(): View
    {
        $project = Project::with([
            'teamLeader',
            'tasks'
        ])->findOrFail($this->project->id);

        return view('exports.project', [
            'project' => $project
        ]);
    }
}
