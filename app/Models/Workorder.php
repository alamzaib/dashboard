<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workorder extends Model
{
    protected $fillable = [
        'workorder_number',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'assigned_to_user_group_id',
        'created_by',
        'task_group_id',
        'workflow_id',
        'is_active',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function assignedUserGroup()
    {
        return $this->belongsTo(UserGroup::class, 'assigned_to_user_group_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function taskGroup()
    {
        return $this->belongsTo(TaskGroup::class);
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}
