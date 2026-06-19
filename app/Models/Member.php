<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'workspace_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'department',
        'role',
        'assigned_to',
    ];

    public function teamLeader()
    {
        return $this->belongsTo(Member::class, 'assigned_to');
    }

    public function teamMembers()
    {
        return $this->hasMany(Member::class, 'assigned_to');
    }

    public function assignedTasks()
    {
        return $this->hasMany(
            Task::class,
            'member_id'
        );
    }

    public function manager()
    {
        return $this->belongsTo(
            Member::class,
            'reports_to'
        );
    }

    public function subordinates()
    {
        return $this->hasMany(
            Member::class,
            'reports_to'
        );
    }
}
