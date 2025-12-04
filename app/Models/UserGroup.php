<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'header_color',
        'logo_path',
    ];

    /**
     * Get the users that belong to the user group.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_user_group');
    }

    /**
     * Get the menus that belong to the user group.
     */
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_user_group');
    }
}
