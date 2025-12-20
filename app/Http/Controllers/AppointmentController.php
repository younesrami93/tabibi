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

    public function index(Request $request)
    {
        $clinic = Auth::user()->clinic;


        $query = Appointment::where('clinic_id', $clinic->id)
            ->with(['patient', 'doctor', 'services','history.user']);
        $appointments = $query->get();

        // 1. Base Query
        // 2. FILTERING LOGIC
        $filterMode = $request->get('filter_mode', 'today_active'); // Default: Today's To-Do List

        if ($filterMode === 'history') {
            // Show past appointments (yesterday and older)
            $query->whereDate('scheduled_at', '<', now());
        } elseif ($filterMode === 'all') {
            // Show everything (Today + Past) - useful for searching
        } else {
            // 'today_active' (Default) or 'today_completed'
            // Default to TODAY
            $query->whereDate('scheduled_at', request('date', now()->format('Y-m-d')));

            if ($filterMode === 'today_active') {
                // Hide finished/cancelled to keep the list clean for work
                $query->whereNotIn('status', ['finished', 'cancelled', 'no_show']);
            }
        }

        // Optional: Filter by specific status if selected in dropdown
        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }


        // 3. SORTING LOGIC (The "Golden Rules")
        // Priority 1: In Consultation (Always Top)
        // Priority 2: Preparing (Next)
        // Priority 3: Urgency (But only if they are waiting/scheduled)
        // Priority 4: Clinic Queue Mode (First-In-First-Out vs Time)

        $query->orderByRaw("
            CASE 
                WHEN status = 'in_consultation' THEN 1
                WHEN status = 'preparing' THEN 2
                WHEN type = 'urgency' AND status IN ('waiting', 'scheduled') THEN 3
                ELSE 4
            END ASC
        ");

        // Secondary Sort: Clinic Preference
        $queueMode = $clinic->settings['queue_mode'] ?? 'scheduled';
        if ($queueMode === 'fifo') {
            $query->orderBy('id', 'asc'); // Ticket System
        } else {
            $query->orderBy('scheduled_at', 'asc'); // Appointment Time System
        }

        $appointments = $query->get();

        // Check for missed past appointments (only relevant if not looking at history)
        $missedCount = 0;
        if ($filterMode === 'today_active') {
            $missedCount = Appointment::where('clinic_id', $clinic->id)
                ->where('scheduled_at', '<', now()->startOfDay())
                ->whereIn('status', ['scheduled', 'waiting'])
                ->count();
        }


        $allServices = MedicalService::where('clinic_id', $clinic->id)->get();

        return view('secretary.appointments', compact('appointments', 'missedCount', 'allServices'));
    }


    public function finish(Request $request, Appointment $appointment)
    {
        $request->validate([
            'services' => 'array',
            'services.*.id' => 'required|exists:medical_services,id',
            'services.*.price' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $appointment) {
            $totalPrice = 0;
            $syncData = [];

            // 1. Prepare Services Data for Sync
            if ($request->has('services')) {
                foreach ($request->services as $serviceData) {
                    $price = $serviceData['price'];
                    $totalPrice += $price;

                    // Prepare pivot data: [service_id => ['price' => 100], ...]
                    $syncData[$serviceData['id']] = ['price' => $price];
                }
            }

            // 2. Sync Services (This replaces old services with the new list)
            $appointment->services()->sync($syncData);

            // 3. Update Appointment Details
            $appointment->update([
                'status' => 'finished',
                'finished_at' => now(),
                'notes' => $request->notes, // Save final medical/admin notes
                'total_price' => $totalPrice,
                // Simple logic: if paid amount >= total, it's paid.
                'is_paid' => ($request->paid_amount >= $totalPrice),
            ]);
        });

        return back()->with('success', 'Appointment completed and invoice generated.');
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

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate(['status' => 'required|in:waiting,preparing,in_consultation,finished,cancelled']);

        $appointment->update([
            'status' => $request->status,
            // If finishing, mark the time
            'finished_at' => $request->status === 'finished' ? now() : null,
            // If starting, mark the time (optional, depending on your flow)
            'started_at' => $request->status === 'in_consultation' ? now() : $appointment->started_at,
        ]);

        return back()->with('success', 'Status updated successfully.');
    }

    // ... existing methods (updateStatus, etc) ...
}