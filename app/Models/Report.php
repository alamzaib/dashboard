<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'filters',
        'columns',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
