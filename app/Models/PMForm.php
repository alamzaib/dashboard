<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMForm extends Model
{
    use HasFactory;

    protected $table = 'pm_forms';

    protected $fillable = [
        'name',
        'description',
        'site_id',
        'form_fields',
        'is_active',
    ];

    protected $casts = [
        'form_fields' => 'array',
        'is_active' => 'boolean',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
