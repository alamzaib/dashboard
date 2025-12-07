<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'contact_person',
        'phone',
        'email',
        'description',
        'longitude',
        'latitude',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function forms()
    {
        return $this->hasMany(PMForm::class, 'site_id');
    }

    public function documents()
    {
        return $this->hasMany(PMDocument::class, 'site_id');
    }
}
