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




    private function getAppointmentsQuery(Request $request)
    {
        $clinic = Auth::user()->clinic;
        $query = Appointment::where('clinic_id', $clinic->id)
            ->with(['patient', 'doctor', 'services', 'history.user']);

        // 1. GLOBAL SEARCH
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($p) use ($search) {
                    $p->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('cin', 'like', "$search");
                })
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // 2. PRESET MODES (Quick Tabs)
        // These serve as "Base Rules" that can be overridden by specific filters below
        $mode = $request->get('quick_filter', 'today_active'); // Default

        if ($mode === 'today_active') {
            // "My Workspace": Today only, hide finished/cancelled
            if (!$request->filled('date_from') && !$request->filled('date_to')) {
                $query->whereDate('scheduled_at', now());
            }
            if (!$request->filled('statuses')) {
                $query->whereNotIn('status', ['finished', 'cancelled', 'no_show']);
            }
        } elseif ($mode === 'history') {
            // "Archives": Anything before today
            if (!$request->filled('date_from') && !$request->filled('date_to')) {
                $query->whereDate('scheduled_at', '<', now());
            }
        }
        // 'all' mode applies no base restrictions

        // 3. ADVANCED FILTERS (Overrides)

        // A. Specific Statuses (e.g., "Show me only Cancelled")
        if ($request->filled('statuses')) {
            $statuses = explode(',', $request->statuses);
            $query->whereIn('status', $statuses);
        }

        // B. Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_at', '<=', $request->date_to);
        }

        // 4. SORTING
        // We keep the logic: In Consultation first, then status, then time.
        $query->orderByRaw("
            CASE 
                WHEN status = 'in_consultation' THEN 1
                WHEN status = 'preparing' THEN 2
                ELSE 4
            END ASC
        ");

        // Chronological sort
        $query->orderBy('scheduled_at', 'desc'); // Newer first is usually better for lists, but switch to 'asc' if you prefer "Next up"

        return $query;
    }

    public function index(Request $request)
    {
        $clinic = Auth::user()->clinic;
        $templates = PrescriptionTemplate::where('clinic_id', $clinic->id)->get();
        $allServices = MedicalService::where('clinic_id', $clinic->id)->get();

        $appointments = $this->getAppointmentsQuery($request)
            ->paginate(15)
            ->appends($request->all());

        // Simple count for missed items (Legacy logic)
        $missedCount = Appointment::where('clinic_id', $clinic->id)
            ->where('scheduled_at', '<', now()->startOfDay())
            ->whereIn('status', ['scheduled', 'waiting'])
            ->count();

        $flashAppointment = null;
        if (session()->has('show_view_modal')) {
            $flashAppointment = Appointment::with(['patient', 'services', 'history.user'])
                ->find(session('show_view_modal'));
        }

        return view('secretary.appointments', compact('appointments', 'flashAppointment', 'missedCount', 'allServices', 'templates'));
    }

    public function fetchTable(Request $request)
    {
        $appointments = $this->getAppointmentsQuery($request)
            ->paginate(15)
            ->appends($request->all());

        return response()->json([
            'html' => view('layouts.partials.appointments_table_rows', compact('appointments'))->render(),
            'pagination' => $appointments->links()->toHtml(),
        ]);
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



    // ... imports

    public function finish(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        // Validate inputs
        $request->validate([
            'price' => 'required|numeric|min:0', // Base Consultation Fee
            'services' => 'array',
            'prescriptions' => 'array',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $appointment) {

            // ====================================================
            // 1. PROCESS SERVICES (Standard & Custom)
            // ====================================================

            // A. Clear old items first (This allows you to edit/update the invoice later)
            // Make sure you have created the AppointmentService model as discussed
            \App\Models\AppointmentService::where('appointment_id', $appointment->id)->delete();

            $servicesTotal = 0;
            $itemsToInsert = [];

            if ($request->has('services')) {
                foreach ($request->services as $serviceData) {
                    $sPrice = $serviceData['price'] ?? 0;
                    $servicesTotal += $sPrice;

                    // Prepare the row for the pivot table
                    $row = [
                        'appointment_id' => $appointment->id,
                        'price' => $sPrice,
                        'created_by' => auth()->id(),
                    ];

                    // Case A: Standard Catalog Service (Has ID)
                    if (isset($serviceData['id'])) {
                        $row['medical_service_id'] = $serviceData['id'];
                        $row['custom_name'] = null;
                    }
                    // Case B: Custom Service (No ID, just text)
                    elseif (isset($serviceData['custom_name'])) {
                        $row['medical_service_id'] = null;
                        $row['custom_name'] = $serviceData['custom_name'];
                    }

                    $itemsToInsert[] = $row;
                }
            }

            // Bulk Insert all items into the updated table
            if (!empty($itemsToInsert)) {
                \App\Models\AppointmentService::insert($itemsToInsert);
            }

            // ====================================================
            // 2. PROCESS PRESCRIPTIONS (Nested JSON)
            // ====================================================
            $finalPrescriptionData = [];
            if ($request->has('prescriptions')) {
                foreach ($request->prescriptions as $block) {
                    $blockItems = [];
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
                    if (!empty($blockItems)) {
                        $finalPrescriptionData[] = [
                            'title' => $block['title'] ?? 'Prescription',
                            'items' => $blockItems
                        ];
                    }
                }
            }

            // ====================================================
            // 3. ROLE-BASED WORKFLOW & FINANCIALS
            // ====================================================
            $user = Auth::user();

            $basePrice = $request->price;
            $grandTotal = $basePrice + $servicesTotal;

            // We keep notes clean now (No need to append custom services text here)
            $finalNotes = $request->notes;

            // --- DOCTOR LOGIC ---
            if ($user->role === 'doctor') {
                $updateData = [
                    'status' => 'pending_payment',
                    'finished_at' => now(),
                    'price' => $basePrice,
                    'total_price' => $grandTotal,
                    'notes' => $finalNotes,
                    'prescription' => $finalPrescriptionData,
                ];
            }
            // --- SECRETARY LOGIC ---
            else {
                $paidAmount = $request->input('paid_amount', 0);
                $remainingDue = $grandTotal - $paidAmount;

                // Update Patient Debt only if Secretary does it
                if ($remainingDue > 0) {
                    $patient = $appointment->patient;
                    $patient->current_balance += $remainingDue;
                    $patient->save();
                }

                $updateData = [
                    'status' => 'finished',
                    'finished_at' => $appointment->finished_at ?? now(),
                    'price' => $basePrice,
                    'total_price' => $grandTotal,
                    'notes' => $finalNotes,
                    'prescription' => $finalPrescriptionData,
                    'is_paid' => ($remainingDue <= 0),
                ];
            }

            // Apply Update
            $appointment->update($updateData);

            // Log History
            \App\Models\AppointmentHistory::create([
                'appointment_id' => $appointment->id,
                'status' => $updateData['status'],
                'changed_by' => $user->id,
            ]);
        });

        $msg = Auth::user()->role === 'doctor'
            ? 'Sent to secretary for payment.'
            : 'Appointment completed and closed.';

        return back()
            ->with('success', $msg)
            ->with('show_view_modal', $appointment->id);
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

    public function showModal($id)
    {
        $appt = Appointment::with(['patient', 'services', 'history.user'])
            ->where('clinic_id', Auth::user()->clinic_id)
            ->findOrFail($id);

        // Returns the FULL modal file (wrapper + content)
        return view('layouts.partials.appointment_details_modal', compact('appt'));
    }

    public function bookModal()
    {
        return view('layouts.partials.book_modal');
    }

    public function showFinishModal($id)
    {
        $clinic_id = Auth::user()->clinic_id;
        $appt = Appointment::with(['patient', 'services', 'history.user'])
            ->where('clinic_id', $clinic_id)
            ->findOrFail($id);

        $templates = PrescriptionTemplate::where('clinic_id', $clinic_id)->get();
        $allServices = MedicalService::where('clinic_id', $clinic_id)->get();


        // Returns the FULL modal file (wrapper + content)
        return view('layouts.partials.finish_appointment', compact('appt', 'templates', 'allServices'));
    }

    // ... existing methods (updateStatus, etc) ...
}