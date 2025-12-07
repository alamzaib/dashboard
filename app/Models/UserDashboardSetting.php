<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDashboardSetting extends Model
{
    protected $fillable = [
        'user_id',
        'chart_settings',
    ];

    protected $casts = [
        'chart_settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
