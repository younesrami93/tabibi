<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

class DocumentPlaceholders
{
    /**
     * Main entry point. Pass any model, and it extracts the data.
     */
    public static function map($model)
    {
        $data = [];

        // 1. Universal System Variables
        $data['{date}'] = now()->format('d/m/Y');
        $data['{time}'] = now()->format('H:i');

        // 2. If it's a Patient Model (Directly)
        if ($model instanceof Patient) {
            $data = array_merge($data, self::mapPatient($model));
        }

        // 3. If it's an Appointment (The Master Record)
        if ($model instanceof Appointment) {
            // Load relationships to avoid N+1 queries
            $model->load(['patient', 'doctor.clinic', 'services']);

            // Map the linked patient
            if ($model->patient) {
                $data = array_merge($data, self::mapPatient($model->patient));
            }

            // Map the linked doctor/clinic
            if ($model->doctor) {
                $data = array_merge($data, self::mapDoctor($model->doctor));
            }

            // Map the appointment details
            $data = array_merge($data, self::mapAppointment($model));
        }

        return $data;
    }

    /**
     * Extract Patient Details
     */
    private static function mapPatient(Patient $p)
    {
        return [
            '{patient_name}' => $p->full_name, // Uses getFullNameAttribute
            '{patient_first_name}' => $p->first_name,
            '{patient_last_name}' => $p->last_name,
            '{patient_phone}' => $p->phone ?? '-',
            '{patient_age}' => $p->age,       // Uses getAgeAttribute
            '{patient_gender}' => ucfirst($p->gender),
            '{patient_dob}' => $p->birth_date ? $p->birth_date->format('d/m/Y') : '-',
            '{patient_balance}' => number_format($p->current_balance, 2) . ' DH',
        ];
    }

    /**
     * Extract Doctor & Clinic Details
     */
    private static function mapDoctor(User $u)
    {
        return [
            '{doctor_name}' => $u->name,
            '{doctor_email}' => $u->email,
            '{doctor_phone}' => $u->phone ?? '',
            '{clinic_name}' => $u->clinic->name ?? 'Medical Clinic',
            // If you have address in clinic settings:
            '{clinic_address}' => $u->clinic->settings['address'] ?? '',
        ];
    }

    /**
     * Extract Appointment Details (Services, Rx, Finances)
     */
    private static function mapAppointment(Appointment $a)
    {
        return [
            // Basic Info
            '{appt_id}' => $a->id,
            '{appt_date}' => $a->scheduled_at->format('d/m/Y'),
            '{appt_time}' => $a->scheduled_at->format('H:i'),
            '{appt_type}' => ucfirst($a->type),
            '{appt_status}' => ucfirst($a->status),

            // Clinical
            '{notes}' => $a->notes ?? '', // The diagnosis/observations

            // Complex Parsing: Services List
            '{services_list}' => self::formatServices($a->services),

            // Complex Parsing: Prescription List
            '{prescription}' => self::formatPrescription($a->prescription),

            // Financials
            '{price_total}' => number_format($a->total_price, 2) . ' DH',
            '{price_base}' => number_format($a->price, 2) . ' DH',
        ];
    }

    /**
     * Helper: Convert Services Collection to String
     * Output Example: "Ultrasound (200 DH), ECG (150 DH)"
     */
    private static function formatServices($services)
    {
        if ($services->isEmpty())
            return '';

        return $services->map(function ($s) {
            // Check if pivot exists, otherwise default to base price
            $price = $s->pivot ? $s->pivot->price : $s->price;
            return "- {$s->name} (" . number_format($price, 0) . ")";
        })->join("\n"); // <--- CHANGED from ', ' to "\n"
    }

    /**
     * Helper: Convert JSON Prescription to HTML List
     * Output Example: 
     * - Paracetamol 100mg (2x/day)
     * - Vitamin C
     */
    private static function formatPrescription($json)
    {
        if (empty($json) || !is_array($json))
            return '';

        $lines = [];

        foreach ($json as $block) {
            // Optional: Add a header for the block if you want
            // if (!empty($block['title'])) $lines[] = strtoupper($block['title']); 

            if (isset($block['items']) && is_array($block['items'])) {
                foreach ($block['items'] as $item) {
                    $name = $item['name'] ?? '';
                    $note = $item['note'] ?? '';

                    // Build the string
                    $line = "- " . $name;
                    if (!empty($note)) {
                        $line .= " (" . $note . ")";
                    }
                    $lines[] = $line;
                }
            }
        }

        // Join with simple newlines. 
        // The Blade view's nl2br() will handle the HTML conversion.
        return implode("\n", $lines);
    }
}