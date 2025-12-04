<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskMember extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'role',
        'task_group_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function taskGroup()
    {
        return $this->belongsTo(TaskGroup::class);
    }
}
