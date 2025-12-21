<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrescriptionTemplate extends Model
{
    use HasFactory,Blameable,SoftDeletes;

    protected $fillable = ['clinic_id', 'type','name', 'items'];

    // Auto-convert JSON to Array
    protected $casts = [
        'items' => 'array',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}