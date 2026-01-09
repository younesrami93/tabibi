<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Appointment extends Model
{
    use HasFactory, SoftDeletes, Blameable;

    protected $guarded = [];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'total_price' => 'decimal:2',
        'prescription' => 'array'
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    // The Pivot: Services done during this appointment
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(MedicalService::class, 'appointment_service')
            ->withPivot('price', 'custom_name');
    }

    public function invoiceItems()
    {
        return $this->hasMany(AppointmentService::class);
    }
    // In Appointment.php

    public function history(): HasMany
    {
        return $this->hasMany(AppointmentHistory::class)->orderBy('created_at', 'desc');
    }

    // "Control" Logic: Is this a follow-up?
    public function parentAppointment()
    {
        return $this->belongsTo(Appointment::class, 'parent_appointment_id');
    }
    public function followUps()
    {
        return $this->hasMany(Appointment::class, 'parent_appointment_id');
    }

    // Helper: Is this a free control?
    public function isControl()
    {
        return $this->type === 'control';
    }

    // RELATIONS
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'billable');
    }

    public function getPaidAmountAttribute()
    {
        return $this->transactions()->where('type', 'income')->sum('amount');
    }

    // Checks if fully paid based on Total Price vs Transactions
    public function getIsSettledAttribute()
    {
        // Floating point comparison safety
        return ($this->total_price - $this->paid_amount) < 0.1;
    }

}