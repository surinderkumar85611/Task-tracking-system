<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'role',
        'department',
        'workspace_id',
        'token',
        'accepted_at',
    ];
}