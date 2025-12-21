<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class CatalogItem extends Model
{
    use HasFactory, SoftDeletes, Blameable;

    protected $fillable = [
        'clinic_id',
        'type',
        'name',
        'form',
        'strength',
        'default_quantity',
        'default_frequency',
        'default_duration',
    ];
    /**
     * Scope to find items visible to a specific clinic
     * (Returns Global items + The Clinic's private items)
     */
    public function scopeForClinic(Builder $query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId)
            ->orWhereNull('clinic_id');
    }
}