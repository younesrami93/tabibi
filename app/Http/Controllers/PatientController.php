<?php

namespace App\Http\Controllers;

use App\Models\Patient;
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

        // Filter Logic
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('cin', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
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
}