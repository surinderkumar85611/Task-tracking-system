<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [

        'workspace_id',
        'project_id',
        'member_id',

        'title',
        'description',

        'priority',
        'status',

        'due_date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
