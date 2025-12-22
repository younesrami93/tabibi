<?php
namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\MedicalService;
use App\Models\PrescriptionTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{

    public function index(Request $request)
    {
        $clinic = Auth::user()->clinic;

        $templates = PrescriptionTemplate::where('clinic_id', $clinic->id)->get();

        $query = Appointment::where('clinic_id', $clinic->id)
            ->with(['patient', 'doctor', 'services', 'history.user']);
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

        return view('secretary.appointments', compact('appointments', 'missedCount', 'allServices', 'templates'));
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


                $clinic = Auth::user()->clinic;

                $defaultPrice = $clinic->config('default_price') ?? 0;


                // 3. CREATE APPOINTMENT
                $appointment = Appointment::create([
                    'clinic_id' => Auth::user()->clinic_id,
                    'doctor_id' => Auth::user()->id,
                    'patient_id' => $patientId,
                    'parent_appointment_id' => $parentId,
                    'type' => $request->type,
                    'status' => 'scheduled',
                    'scheduled_at' => $request->scheduled_at,
                    'total_price' => $totalPrice,
                    'price' => $defaultPrice,
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




    public function finish(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate([
            'price' => 'required|numeric|min:0', // Base Consultation Fee
            'services' => 'array',
            'prescriptions' => 'array',          // <--- FIXED: Plural (matches JS)
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $appointment) {

            // ====================================================
            // 1. PROCESS SERVICES (Standard & Custom)
            // ====================================================
            $syncData = [];
            $servicesTotal = 0;
            $customServicesList = []; // To store names of custom items

            if ($request->has('services')) {
                foreach ($request->services as $serviceData) {
                    $sPrice = $serviceData['price'] ?? 0;
                    $servicesTotal += $sPrice;

                    // Case A: Standard Catalog Service (Has ID)
                    if (isset($serviceData['id'])) {
                        $syncData[$serviceData['id']] = ['price' => $sPrice];
                    }
                    // Case B: Custom Service (No ID, just text)
                    elseif (isset($serviceData['custom_name'])) {
                        $customServicesList[] = $serviceData['custom_name'] . ' (' . $sPrice . ' DH)';
                    }
                }
            }

            // Sync Standard Services to Pivot Table
            $appointment->services()->sync($syncData);

            // ====================================================
            // 2. PROCESS PRESCRIPTIONS (Nested JSON)
            // ====================================================
            $finalPrescriptionData = [];

            // We look for 'prescriptions' (Plural) as sent by the new UI
            if ($request->has('prescriptions')) {
                foreach ($request->prescriptions as $block) {

                    $blockItems = [];
                    // Check if this block has items
                    if (isset($block['items']) && is_array($block['items'])) {
                        foreach ($block['items'] as $item) {
                            if (!empty($item['name'])) {
                                $blockItems[] = [
                                    'catalog_item_id' => $item['catalog_item_id'] ?? null,
                                    'name' => $item['name'],
                                    'note' => $item['note'] ?? '',
                                ];
                            }
                        }
                    }

                    // Only save the block if it has items
                    if (!empty($blockItems)) {
                        $finalPrescriptionData[] = [
                            'title' => $block['title'] ?? 'Prescription',
                            'items' => $blockItems
                        ];
                    }
                }
            }

            // ====================================================
            // 3. FINANCIALS & SAVING
            // ====================================================
            $basePrice = $request->price;
            $grandTotal = $basePrice + $servicesTotal;
            $paidAmount = $request->input('paid_amount', 0);
            $remainingDue = $grandTotal - $paidAmount;

            // Handle Credit
            if ($remainingDue > 0) {
                $patient = $appointment->patient;
                $patient->current_balance += $remainingDue;
                $patient->save();
            }

            // Append Custom Services to Notes (so we don't lose record of them)
            $finalNotes = $request->notes;
            if (!empty($customServicesList)) {
                $finalNotes .= "\n\n[Custom Services]: " . implode(', ', $customServicesList);
            }

            // Update Appointment
            $appointment->update([
                'status' => 'finished',
                'finished_at' => now(),
                'price' => $basePrice,
                'total_price' => $grandTotal,
                'notes' => $finalNotes,
                'prescription' => $finalPrescriptionData, // Save the Nested JSON
                'is_paid' => ($remainingDue <= 0),
            ]);

            // Log History
            \App\Models\AppointmentHistory::create([
                'appointment_id' => $appointment->id,
                'status' => 'finished',
                'changed_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Appointment Completed Successfully.');
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