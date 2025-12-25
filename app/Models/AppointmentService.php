<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointmentService extends Pivot
{
    use HasFactory, SoftDeletes, Blameable;

    protected $table = 'appointment_service';

    public $timestamps = false; // Pivot tables usually don't have timestamps unless defined

    protected $fillable = [
        'appointment_id',
        'medical_service_id',
        'custom_name',
        'price',
        'created_by'
    ];

    // Helper to get the display name
    public function getNameAttribute()
    {
        // If it has a catalog link, use that name. Otherwise, use custom name.
        if ($this->medical_service_id && $this->service) {
            return $this->service->name;
        }
        return $this->custom_name;
    }

    public function service()
    {
        return $this->belongsTo(MedicalService::class, 'medical_service_id');
    }
}