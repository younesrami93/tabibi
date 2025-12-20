<?php


namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clinic extends Model
{
    use HasFactory, SoftDeletes,Blameable;
    /**
     * The attributes that aren't mass assignable.
     * Setting this to an empty array allows us to fill any field (name, price, etc.) safely.
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     * Ensures 'is_active' is a real boolean and dates are Carbon objects.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'subscription_expires_at' => 'date',
        'subscription_price' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'settings' => 'array',
    ];



    public $defaultSettings = [
        'currency_code' => 'MAD',
        'currency_symbol' => 'DH',
        'country' => 'Morocco',
        'timezone' => 'Africa/Casablanca',
        'calendar_start_time' => '09:00',
        'calendar_end_time' => '18:00',
        'slot_duration' => 30, // minutes
        'language' => 'fr',
    ];


    public function config($key)
    {
        $allSettings = array_merge($this->defaultSettings, $this->settings ?? []);
        return data_get($allSettings, $key);
    }


    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    /**
     * Get the users (Doctors, Secretaries) for this clinic.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the patients belonging to this clinic.
     */
    /*public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }*/

    /**
     * Get the appointments belonging to this clinic.
     */
    /*public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }*/

    /* -----------------------------------------------------------------
     |  Accessors & Helpers (Business Logic)
     | -----------------------------------------------------------------
     */

    /**
     * Calculate the remaining balance (Credit).
     * Usage in Blade: {{ $clinic->balance_due }}
     */
    public function getBalanceDueAttribute(): float
    {
        // Example: Price 2000 - Paid 1500 = 500 (Rest)
        return $this->subscription_price - $this->total_paid;
    }

    /**
     * Check if the clinic has fully paid their subscription.
     * Usage: if($clinic->is_fully_paid)
     */
    public function getIsFullyPaidAttribute(): bool
    {
        // We use a tiny number (0.01) to avoid floating point errors
        return $this->balance_due < 0.01;
    }

    /**
     * Check if the subscription is currently active (Not Expired + Active Flag).
     */
    public function hasActiveSubscription(): bool
    {
        // 1. Must be marked active in DB
        if (!$this->is_active) {
            return false;
        }

        // 2. If date is null, we assume it's a "Lifetime" or "Manual" plan that doesn't expire
        if ($this->subscription_expires_at === null) {
            return true;
        }

        // 3. Check if date is in the future
        return $this->subscription_expires_at->isFuture() || $this->subscription_expires_at->isToday();
    }

    /**
     * Get a color status for the Bootstrap Badge.
     * Usage: <span class="badge bg-{{ $clinic->payment_status_color }}">
     */
    public function getPaymentStatusColorAttribute(): string
    {
        if ($this->subscription_price == 0)
            return 'info';    // Free/Trial
        if ($this->is_fully_paid)
            return 'success';           // Paid
        if ($this->total_paid > 0)
            return 'warning';          // Partial Payment (Credit exists)
        return 'danger';                                      // Not paid at all
    }
}