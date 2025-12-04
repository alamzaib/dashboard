<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reference_id',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::created(function (User $user) {
            // Generate reference_id if not already set: 4 random uppercase alnum + primary ID
            if (empty($user->reference_id)) {
                $user->reference_id = strtoupper(Str::random(4)) . $user->id;
                // Use saveQuietly to avoid triggering events again
                $user->saveQuietly();
            }
        });
    }

    /**
     * Get the user groups that belong to the user.
     */
    public function userGroups()
    {
        return $this->belongsToMany(UserGroup::class, 'user_user_group');
    }

    /**
     * Get the permission groups that belong to the user.
     */
    public function permissionGroups()
    {
        return $this->belongsToMany(PermissionGroup::class, 'user_permission_group')
            ->with('project', 'permissions');
    }

    public function taskGroups()
    {
        return $this->belongsToMany(TaskGroup::class, 'task_group_user');
    }
}
