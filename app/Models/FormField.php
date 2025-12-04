<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'field_name',
        'field_source',
        'display_order',
        'is_required',
        'is_visible',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_visible' => 'boolean',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}

