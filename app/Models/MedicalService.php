<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalService extends Model
{
    use HasFactory, SoftDeletes, Blameable;
    protected $guarded = [];


    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
    ];


    public function getFullLabelAttribute()
    {
        $codeStr = $this->code ? "[{$this->code}] " : "";
        return "{$codeStr}{$this->name} - " . number_format($this->price, 2) . " DH";
    }
}
