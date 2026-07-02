<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'description',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
