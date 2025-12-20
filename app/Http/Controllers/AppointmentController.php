<?php
namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\MedicalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index()
    {
        // Default to Today's Agenda
        $appointments = Appointment::where('clinic_id', Auth::user()->clinic_id)
            ->with(['patient', 'doctor'])
            // Filter by date (defaults to today if no date in URL)
            ->whereDate('scheduled_at', request('date', now()->format('Y-m-d')))
            ->orderBy('scheduled_at')
            ->get();

        return view('admin.appointments.index', compact('appointments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // Either select a patient OR provide new patient details
            'patient_id' => 'nullable|exists:patients,id',
            'new_first_name' => 'required_without:patient_id|nullable|string',
            'new_last_name' => 'required_without:patient_id|nullable|string',

            'scheduled_at' => 'required|date',
            'type' => 'required|in:consultation,control,urgency',
        ]);

        try {
            DB::transaction(function () use ($request) {

                // 1. HANDLE PATIENT (Existing vs New)
                $patientId = $request->patient_id;

                if (!$patientId) {
                    // Create New Patient on the Fly
                    $patient = Patient::create([
                        'clinic_id' => Auth::user()->clinic_id,
                        'first_name' => $request->new_first_name,
                        'last_name' => $request->new_last_name,
                        'phone' => $request->new_phone,
                        'gender' => $request->new_gender ?? 'male', // Default to save time
                        'current_balance' => 0,
                    ]);
                    $patientId = $patient->id;
                }

                // 2. HANDLE CONTROL LOGIC (Auto-Link)
                $parentId = null;
                $totalPrice = 0;
                $servicesToAttach = [];

                if ($request->type === 'control') {
                    // Try to find the last appointment to link to
                    $lastAppt = Appointment::where('patient_id', $patientId)
                        ->where('type', '!=', 'control') // Don't link control to control
                        ->latest('scheduled_at')
                        ->first();

                    $parentId = $lastAppt ? $lastAppt->id : null;
                    $totalPrice = 0; // Controls are free
                } else {
                    // Standard Consultation Price logic (optional default)
                    // If services selected, sum them. If not, maybe 0 or base price.
                    if ($request->filled('service_ids')) {
                        $services = MedicalService::whereIn('id', $request->service_ids)->get();
                        $totalPrice = $services->sum('price');
                        $servicesToAttach = $services;
                    }
                }

                // 3. CREATE APPOINTMENT
                $appointment = Appointment::create([
                    'clinic_id' => Auth::user()->clinic_id,
                    'doctor_id' => Auth::user()->id, // Or assign to specific doctor
                    'patient_id' => $patientId,
                    'parent_appointment_id' => $parentId,
                    'type' => $request->type,
                    'status' => 'scheduled',
                    'scheduled_at' => $request->scheduled_at,
                    'total_price' => $totalPrice,
                    'notes' => $request->notes,
                ]);

                // 4. ATTACH SERVICES
                foreach ($servicesToAttach as $service) {
                    $appointment->services()->attach($service->id, ['price' => $service->price]);
                }
            });

            return back()->with('success', 'Appointment booked successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    // ... existing methods (updateStatus, etc) ...
}