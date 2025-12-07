<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PMDocument extends Model
{
    use HasFactory;

    protected $table = 'pm_documents';

    protected $fillable = [
        'site_id',
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

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
