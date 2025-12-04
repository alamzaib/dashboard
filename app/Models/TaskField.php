<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskField extends Model
{
    use HasFactory;

    protected $fillable = [
        'field_name',
        'field_label',
        'field_type',
        'field_options',
        'is_required',
        'is_active',
        'display_order',
        'validation_rules',
    ];

    protected $casts = [
        'field_options' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];
}

