<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ClinicImage extends Model
{
    use HasFactory, SoftDeletes, Blameable;

    protected $fillable = [
        'clinic_id',
        'path',
        'filename',
        'mime_type',
        'size',
        'created_by'
    ];

    /**
     * Helper to get the full URL for the frontend.
     */
    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }
}