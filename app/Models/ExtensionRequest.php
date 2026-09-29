<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtensionRequest extends Model
{
    protected $fillable = [
        'project_id',
        'requested_by',
        'requested_deadline',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        
    ];
protected $casts = [
    'requested_deadline' => 'date',
    'reviewed_at' => 'datetime',
];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}