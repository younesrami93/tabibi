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

    public static function map($model, $options = [])
    {
        $data = [];

        // 1. Universal System Variables
        $data['{date}'] = now()->format('d/m/Y');
        $data['{time}'] = now()->format('H:i');

        // 2. If it's a Patient Model
        if ($model instanceof Patient) {
            $data = array_merge($data, self::mapPatient($model));
        }

        // 3. If it's an Appointment
        if ($model instanceof Appointment) {
            $model->load(['patient', 'doctor.clinic', 'invoiceItems']);

            if ($model->patient) {
                $data = array_merge($data, self::mapPatient($model->patient));
            }

            if ($model->doctor) {
                $data = array_merge($data, self::mapDoctor($model->doctor));
            }

            // [UPDATED] Pass options to mapAppointment
            $data = array_merge($data, self::mapAppointment($model, $options));
        }

        return $data;
    }

    private static function mapPatient(Patient $p)
    {
        return [
            '{patient_name}' => $p->full_name,
            '{patient_first_name}' => $p->first_name,
            '{patient_last_name}' => $p->last_name,
            '{patient_phone}' => $p->phone ?? '-',
            '{patient_age}' => $p->age,
            '{patient_gender}' => ucfirst($p->gender),
            '{patient_dob}' => $p->birth_date ? $p->birth_date->format('d/m/Y') : '-',
            '{patient_balance}' => number_format($p->current_balance, 2) . ' DH',
        ];
    }

    private static function mapDoctor(User $u)
    {
        return [
            '{doctor_name}' => $u->name,
            '{doctor_email}' => $u->email,
            '{doctor_phone}' => $u->phone ?? '',
            '{clinic_name}' => $u->clinic->name ?? 'Medical Clinic',
            '{clinic_address}' => $u->clinic->settings['address'] ?? '',
        ];
    }

    /**
     * [UPDATED] Handle Specific Prescription Filtering
     */
    private static function mapAppointment(Appointment $a, $options = [])
    {
        // Check if we want a specific block (Rx 1, Rx 2...)
        $targetIndex = $options['rx_index'] ?? null;

        return [
            '{appt_id}' => $a->id,
            '{appt_date}' => $a->scheduled_at->format('d/m/Y'),
            '{appt_time}' => $a->scheduled_at->format('H:i'),
            '{appt_type}' => ucfirst($a->type),
            '{appt_status}' => ucfirst($a->status),
            '{notes}' => $a->notes ?? '',
            '{services_list}' => self::formatServices($a->invoiceItems),
            // [UPDATED] Pass the index to the formatter
            '{prescription}' => self::formatPrescription($a->prescription, $targetIndex),

            '{price_total}' => number_format($a->total_price, 2) . ' DH',
            '{price_base}' => number_format($a->price, 2) . ' DH',
        ];
    }

    private static function formatServices($services)
    {
        if ($services->isEmpty())
            return '';

        return $services->map(function ($item) {
            return "- {$item->name} (" . number_format($item->price, 2) . " DH)";
        })->join("\n");
    }

    /**
     * [UPDATED] Helper: Filter by Index if provided
     */
    private static function formatPrescription($json, $specificIndex = null)
    {
        if (empty($json) || !is_array($json))
            return '';

        $lines = [];

        // 1. Determine which blocks to process
        // If index is provided and valid, filter to just that one block
        if ($specificIndex !== null && isset($json[$specificIndex])) {
            $blocksToProcess = [$json[$specificIndex]];
        } else {
            // Otherwise process all blocks (fallback or legacy behavior)
            $blocksToProcess = $json;
        }

        // 2. Generate Text
        foreach ($blocksToProcess as $block) {

            // Optional: Include Title if printing multiple, but usually hidden for single Rx
            // if (count($blocksToProcess) > 1 && !empty($block['title'])) {
            //    $lines[] = strtoupper($block['title']);
            // }

            if (isset($block['items']) && is_array($block['items'])) {
                foreach ($block['items'] as $item) {
                    $name = $item['name'] ?? '';
                    $note = $item['note'] ?? '';

                    $line = "- " . $name;
                    if (!empty($note)) {
                        $line .= " (" . $note . ")";
                    }
                    $lines[] = $line;
                }
            }
        }

        return implode("\n", $lines);
    }
}