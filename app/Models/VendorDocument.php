<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'name',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'document_type',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}

