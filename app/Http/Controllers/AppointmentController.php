<?php
namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentHistory;
use App\Models\AppointmentService;
use App\Models\Patient;
use App\Models\MedicalService;
use App\Models\PrescriptionTemplate;
use App\Models\Transaction;
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
        // Note: Ensure your JS sends 'quick_filter' or 'filter_mode' matching this key.
        // Based on your previous blade files, you might be using 'filter_mode', 
        // so we check both to be safe, defaulting to 'today_active'.
        $mode = $request->get('quick_filter', $request->get('filter_mode', 'today_active'));

        if ($mode === 'today_active') {
            // "My Workspace": Today only, hide finished/cancelled
            if (!$request->filled('date_from') && !$request->filled('date_to')) {
                $query->whereDate('scheduled_at', now());
            }
            if (!$request->filled('statuses')) {
                $query->whereNotIn('status', ['finished', 'cancelled', 'no_show']);
            }
        } elseif ($mode === 'finished') {
            // "Finished": Show only completed appointments
            $query->where('status', 'finished');

        } elseif ($mode === 'cancelled') {
            // "Cancelled": Show only cancelled appointments
            $query->where('status', 'cancelled');

        } elseif ($mode === 'history') {
            // "Archives": Anything before today
            if (!$request->filled('date_from') && !$request->filled('date_to')) {
                $query->whereDate('scheduled_at', '<', now());
            }
        }
        // 'all' mode applies no base restrictions

        // 3. ADVANCED FILTERS (Overrides)

        // A. Specific Statuses (e.g., "Show me only Cancelled" selected in dropdown)
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
        $query->orderBy('scheduled_at', 'desc');

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
            'new_cin' => 'nullable|string|max:20',
            'new_birth_date' => 'nullable|date',
            'new_mutuelle_provider' => 'nullable|string',
            'new_mutuelle_number' => 'nullable|string',
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
                        'cin' => $request->new_cin,
                        'birth_date' => $request->new_birth_date,
                        'gender' => $request->new_gender ?? 'male', // Default to save time
                        'mutuelle_provider' => $request->new_mutuelle_provider,
                        'mutuelle_number' => $request->new_mutuelle_number,
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

            // Clear old items first to allow editing/re-saving
            AppointmentService::where('appointment_id', $appointment->id)->delete();

            $servicesTotal = 0;
            $itemsToInsert = [];

            if ($request->has('services')) {
                foreach ($request->services as $serviceData) {
                    $sPrice = $serviceData['price'] ?? 0;
                    $servicesTotal += $sPrice;

                    $row = [
                        'appointment_id' => $appointment->id,
                        'price' => $sPrice,
                        'created_by' => auth()->id(),
                    ];

                    if (isset($serviceData['id'])) {
                        $row['medical_service_id'] = $serviceData['id'];
                        $row['custom_name'] = null;
                    } elseif (isset($serviceData['custom_name'])) {
                        $row['medical_service_id'] = null;
                        $row['custom_name'] = $serviceData['custom_name'];
                    }

                    $itemsToInsert[] = $row;
                }
            }

            if (!empty($itemsToInsert)) {
                AppointmentService::insert($itemsToInsert);
            }

            // ====================================================
            // 2. PROCESS PRESCRIPTIONS
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
            // 3. FINANCIALS & TRANSACTIONS (Centralized)
            // ====================================================
            $user = Auth::user();

            $basePrice = $request->price;
            $grandTotal = $basePrice + $servicesTotal;

            // A. Create Transaction (The Source of Truth)
            // We record exactly what was paid NOW
            $paidNow = $request->input('paid_amount', 0);

            if ($paidNow > 0) {
                Transaction::create([
                    'clinic_id' => $user->clinic_id,
                    'user_id' => $user->id,
                    'patient_id' => $appointment->patient_id,
                    'billable_type' => Appointment::class,
                    'billable_id' => $appointment->id,
                    'type' => 'income',
                    'amount' => $paidNow,
                    'category' => 'consultation',
                    'transaction_date' => now(),
                    'notes' => 'Payment received at finish',
                ]);
            }

            // B. Sync Appointment Cache (Total & Paid Status)
            // We calculate total paid by summing ALL transactions for this appointment (including the one we just made)
            $totalPaidReal = Transaction::where('billable_type', Appointment::class)
                ->where('billable_id', $appointment->id)
                ->where('type', 'income')
                ->sum('amount');

            $remainingDue = $grandTotal - $totalPaidReal;

            // 3. Ensure we don't store negative due amounts (if overpaid)
            $storedDue = max(0, $remainingDue);

            $isPaid = $storedDue < 0.1; // Tolerance for float math

            // C. Update Patient Balance (Legacy Support)
            // If there is still a remaining balance, we add it to the patient's debt.
            // (Note: This logic assumes 'finish' is a one-time charge event. 
            //  It adds the Net Unpaid amount of this session to the patient's total debt).
            if ($user->role !== 'doctor' && $remainingDue > 0) {
                // Calculation: Cost ($grandTotal) - Payment ($paidNow) = New Debt Added
                // We use $paidNow (this session's payment) to calculate the immediate debt increase.
                $netDebtIncrease = $grandTotal - $paidNow;

                if ($netDebtIncrease > 0) {
                    $appointment->patient->increment('current_balance', $netDebtIncrease);
                }
            }

            $updateData = [
                'status' => 'finished',
                'finished_at' => $appointment->finished_at ?? now(),
                'price' => $basePrice,
                'total_price' => $grandTotal,
                'is_paid' => $isPaid,
                'notes' => $request->notes,
                'prescription' => $finalPrescriptionData,
                'paid_amount' => $totalPaidReal,
                'due_amount' => $storedDue,
            ];

            // Doctors usually just finish the medical part; Secretaries handle the money/closing.
            if ($user->role === 'doctor') {
                $updateData['status'] = 'pending_payment';
            }

            $appointment->update($updateData);

            AppointmentHistory::create([
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


    public function addPayment(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        // 1. Validation: Ensure we don't pay more than what is owed
        $maxPayable = $appointment->due_amount;

        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $maxPayable],
            'method' => 'required|in:cash,card,check,transfer',
        ], [
            'amount.max' => "You cannot pay more than the remaining debt (" . number_format($maxPayable, 2) . " DH).",
        ]);

        DB::transaction(function () use ($request, $appointment) {

            // 2. Create the Transaction Record
            Transaction::create([
                'clinic_id' => $appointment->clinic_id,
                'user_id' => Auth::id(),
                'patient_id' => $appointment->patient_id,
                'billable_type' => Appointment::class,
                'billable_id' => $appointment->id,
                'type' => 'income',
                'amount' => $request->amount,
                'category' => 'consultation',
                'payment_method' => $request->method,
                'transaction_date' => now(),
                'notes' => 'Partial/Full payment for Appointment #' . $appointment->id,
            ]);

            // 3. Update Appointment Financials (Cache Columns)
            // We increment paid_amount and decrement due_amount safely
            $appointment->increment('paid_amount', $request->amount);
            $appointment->decrement('due_amount', $request->amount);

            // 4. Update Status if fully paid
            if ($appointment->due_amount <= 0.1) {
                $appointment->is_paid = true;
                if ($appointment->status === 'pending_payment') {
                    $appointment->status = 'finished';
                }
            }
            $appointment->save();

            // 5. Update Patient Global Balance (Reduce their debt)
            $appointment->patient->decrement('current_balance', $request->amount);
        });

        return back()->with('success', 'Payment of ' . $request->amount . ' DH recorded successfully.');
    }


    // ... existing methods (updateStatus, etc) ...
}