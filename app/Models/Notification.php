<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
    'user_id',
    'workspace_id',
    'type',
    'title',
    'message',
    'data',
    'is_read',

    'created_by',
    'created_by_role',
];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}