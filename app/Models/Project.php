<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Task;
use App\Models\Member;

class Project extends Model
{
    protected $fillable = [
        'workspace_id',
        'name',
        'status',
        'deadline',
        'description',
        'team_leader_id',
        'progress',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function teamLeader()
    {
        return $this->belongsTo(
            Member::class,
            'team_leader_id'
        );
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
