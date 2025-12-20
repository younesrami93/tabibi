<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory, SoftDeletes, Blameable;

    protected $guarded = [];

    protected $casts = [
        'birth_date' => 'date',
        'current_balance' => 'decimal:2',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | -----------------------------------------------------------------
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute()
    {
        return $this->birth_date ? $this->birth_date->age . ' yrs' : '-';
    }

    public function lastAppointment(): HasOne
    {
        return $this->hasOne(Appointment::class)->latestOfMany('scheduled_at');
    }


    // ...

    // ...
    public function nextControl(): HasOne
    {
        return $this->hasOne(Appointment::class)
            ->where('type', 'control')
            // We look for ANY status that is NOT finished or cancelled
            ->whereIn('status', ['scheduled', 'waiting'])
            // REMOVED: ->where('scheduled_at', '>=', now()) 
            // We want the oldest one because that's the one they are late for!
            ->oldestOfMany('scheduled_at');
    }
    // ...

    
    // ...

}