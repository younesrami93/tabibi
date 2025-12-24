<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes, Blameable;

    protected $fillable = [
        'clinic_id',
        'user_id',
        'name',
        'role',
        'content',
        'created_by' // Blameable fills this, but it needs to be fillable
    ];

    protected $casts = [
        'content' => 'array',
    ];



    const ROLE_GENERAL = 'general';
    const ROLE_PRESCRIPTION = 'prescription';
    const ROLE_INVOICE = 'invoice';
    const ROLE_CERTIFICATE = 'medical_certificate';
    const ROLE_REFERRAL = 'referral_letter';
    const ROLE_REPORT = 'medical_report';
    const ROLE_CONSENT = 'consent_form';

    /**
     * Returns the list of valid roles for validation
     */
    public static function getRoles()
    {
        return [
            self::ROLE_GENERAL,
            self::ROLE_PRESCRIPTION,
            self::ROLE_INVOICE,
            self::ROLE_CERTIFICATE,
            self::ROLE_REFERRAL,
            self::ROLE_REPORT,
            self::ROLE_CONSENT,
        ];
    }

    public static function getRoleLabels()
    {
        return [
            self::ROLE_GENERAL => 'General Document',
            self::ROLE_PRESCRIPTION => 'Prescription (Ordonnance)',
            self::ROLE_INVOICE => 'Invoice (Facture)',
            self::ROLE_CERTIFICATE => 'Medical Certificate',
            self::ROLE_REFERRAL => 'Referral Letter (Orientation)',
            self::ROLE_REPORT => 'Medical Report',
            self::ROLE_CONSENT => 'Consent Form',
        ];
    }


    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    // Belongs to the Doctor (Owner)
    public function doctor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scope to get documents for the current user's clinic
    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }
}
