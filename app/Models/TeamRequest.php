<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamRequest extends Model
{
    protected $fillable = [
        'workspace_id',
        'leader_id',
        'type',
        'member_email',
        'department',
        'eligibility',
        'reason',
        'status'
    ];
}