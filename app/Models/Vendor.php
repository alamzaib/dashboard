<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'contact_person',
        'tax_id',
        'notes',
        'status',
    ];

    public function documents()
    {
        return $this->hasMany(VendorDocument::class);
    }
}

