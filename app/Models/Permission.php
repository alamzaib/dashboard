<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'permission_group_id',
        'name',
        'read',
        'write',
        'update',
        'delete',
    ];

    protected $casts = [
        'read' => 'boolean',
        'write' => 'boolean',
        'update' => 'boolean',
        'delete' => 'boolean',
    ];

    /**
     * Get the permission group that owns the permission.
     */
    public function permissionGroup()
    {
        return $this->belongsTo(PermissionGroup::class);
    }
}
