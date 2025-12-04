<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the permission groups for the project.
     */
    public function permissionGroups()
    {
        return $this->hasMany(PermissionGroup::class);
    }
}
