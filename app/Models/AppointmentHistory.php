<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentHistory extends Model
{
    protected $fillable = ['appointment_id', 'status', 'changed_by'];

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}