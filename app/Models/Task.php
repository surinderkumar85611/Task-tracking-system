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
        'notes',
        'allocated_duration',
        'timer_started_at',
        'review'
    ];

    protected $casts = [
        'notes' => 'array',
        'member_id' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getMembersAttribute()
    {
        if (empty($this->member_id) || !is_array($this->member_id)) {
            return collect();
        }

        return Member::whereIn('id', $this->member_id)->get();
    }

    /**
     * Append this custom attribute to the model's array/JSON output automatically
     */
    protected $appends = ['assigned_members'];

    public function getAssignedMembersAttribute()
    {
        return $this->members;
    }
}
