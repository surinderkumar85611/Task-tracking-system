<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'workspace_id',
        'name',
        'description',
        'leader_id',
    ];

    public function leader()
    {
        return $this->belongsTo(
            Member::class,
            'leader_id'
        );
    }

    public function members()
    {
        return $this->hasMany(
            Member::class,
            'team_id'
        );
    }
}