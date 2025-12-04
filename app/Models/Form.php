<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'is_active',
        'is_public',
        'public_key',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function formFields()
    {
        return $this->hasMany(FormField::class)->orderBy('display_order');
    }
}

