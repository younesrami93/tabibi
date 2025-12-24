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
