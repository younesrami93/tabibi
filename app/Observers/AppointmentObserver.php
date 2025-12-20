<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\AppointmentHistory;
use Auth;

class AppointmentObserver
{
    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        $this->logHistory($appointment);
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {
        if ($appointment->isDirty('status')) {
            $this->logHistory($appointment);
        }
    }

    private function logHistory(Appointment $appointment)
    {
        AppointmentHistory::create([
            'appointment_id' => $appointment->id,
            'status' => $appointment->status,
            'changed_by' => Auth::check() ? Auth::id() : $appointment->created_by, // Fallback if automated
        ]);
    }

}
