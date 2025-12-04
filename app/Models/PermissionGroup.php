<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'description',
    ];

    /**
     * Get the project that owns the permission group.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the permissions for the permission group.
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * Get the users that belong to the permission group.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permission_group');
    }
}
