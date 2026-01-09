<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Transaction;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    /**
     * Display the Patients List with Search.
     */
    public function index(Request $request)
    {
        $query = Patient::where('clinic_id', Auth::user()->clinic_id)
            ->with(['lastAppointment', 'nextControl'])
            ->withCount('appointments');

        // 1 Filter Logic
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('cin', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // 2. NEW: Balance Filter Logic
        if ($request->filled('balance_filter')) {
            if ($request->balance_filter === 'debt') {
                $query->where('current_balance', '>', 0); // Show only those who owe money
            } elseif ($request->balance_filter === 'clear') {
                $query->where('current_balance', '<=', 0);
            }
        }


        // Sort by newest added by default
        $patients = $query->latest()->paginate(15);

        return view('secretary.patients', compact('patients'));
    }


    public function search(Request $request)
    {
        $search = $request->query('q');

        // Return empty list if search is too short to avoid massive queries
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $patients = Patient::where('clinic_id', Auth::user()->clinic_id)
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('cin', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'phone']);

        return response()->json($patients);
    }

    /**
     * Store a new Patient.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            // CIN should be unique per clinic, but maybe not globally (duplicate patient across diff doctors is allowed)
            'cin' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:male,female',
            'mutuelle_provider' => 'nullable|string',
        ]);

        Patient::create([
            'clinic_id' => Auth::user()->clinic_id, // Vital Security Link
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'cin' => $request->cin,
            'phone' => $request->phone,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'mutuelle_provider' => $request->mutuelle_provider,
            'mutuelle_number' => $request->mutuelle_number,
            'current_balance' => 0, // Starts at 0
        ]);

        return back()->with('success', 'Patient file created successfully.');
    }


    public function show(Patient $patient)
    {
        // Security Check: Ensure patient belongs to this clinic
        if ($patient->clinic_id !== Auth::user()->clinic_id) {
            abort(403);
        }

        // Load relationships for the History Tab
        $patient->load([
            'appointments' => function ($q) {
                $q->orderBy('scheduled_at', 'desc');
            },
            /*'prescriptions' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }*/
        ]);

        return view('secretary.patient_show', compact('patient'));
    }


    /**
     * Update an existing Patient.
     */
    public function update(Request $request, $id)
    {
        $patient = Patient::where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->firstOrFail();

        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'gender' => 'required|in:male,female',
        ]);

        $patient->update($request->all());

        return back()->with('success', 'Patient details updated.');
    }

    /**
     * Soft Delete a Patient.
     */
    public function destroy($id)
    {
        $patient = Patient::where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->firstOrFail();

        $patient->delete(); // Soft Delete

        return back()->with('success', 'Patient moved to archives (Trash).');
    }


    public function addPayment(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        // Validate: Cannot pay more than total debt
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $patient->current_balance,
            'method' => 'required|in:cash,card,check,transfer',
        ]);

        DB::transaction(function () use ($request, $patient) {
            $amountRemaining = $request->amount;

            // 1. Create ONE Transaction for the Caisse (Linked to Patient)
            // We link to Patient so we know who paid, but we don't spam the finance log with 10 small appointment transactions.
            Transaction::create([
                'clinic_id' => auth()->user()->clinic_id,
                'user_id' => auth()->id(),
                'patient_id' => $patient->id,
                'billable_type' => Patient::class,
                'billable_id' => $patient->id,
                'type' => 'income',
                'amount' => $amountRemaining,
                'category' => 'debt_payment',
                'payment_method' => $request->method,
                'transaction_date' => now(),
                'notes' => 'Global debt payment (FIFO)',
            ]);

            // 2. FIFO Logic: Find unpaid appointments (Oldest First)
            $unpaidAppts = $patient->appointments()
                ->where('due_amount', '>', 0)
                ->orderBy('scheduled_at', 'asc') // Oldest first
                ->get();

            foreach ($unpaidAppts as $appt) {
                if ($amountRemaining <= 0)
                    break;

                // How much can we pay towards this specific appointment?
                $pay = min($amountRemaining, $appt->due_amount);

                // Update Appointment Columns
                $appt->increment('paid_amount', $pay);
                $appt->decrement('due_amount', $pay);

                // Check if fully paid
                if ($appt->due_amount <= 0.1) {
                    $appt->is_paid = true;
                    // Auto-close if it was pending payment
                    if ($appt->status === 'pending_payment') {
                        $appt->status = 'finished';
                    }
                }
                $appt->save();

                $amountRemaining -= $pay;
            }

            // 3. Update Patient Global Debt
            $patient->decrement('current_balance', $request->amount);
        });

        return back()->with('success', 'Payment recorded and distributed to oldest debts.');
    }

}