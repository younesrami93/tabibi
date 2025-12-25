<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class DashboardController extends Controller
{


    // ... existing code ...



    public function globalSearch(Request $request)
    {
        $query = trim($request->get('query'));
        if (empty($query))
            return response()->json([]);

        $results = [];
        $clinicId = \Illuminate\Support\Facades\Auth::user()->clinic_id;

        // ==========================================
        // PRIORITY 1: APPOINTMENTS
        // Supports: "5050" or "a5050"
        // ==========================================
        $apptId = null;
        if (preg_match('/^a(\d+)$/i', $query, $matches)) {
            $apptId = $matches[1];
        } elseif (is_numeric($query)) {
            $apptId = $query;
        }

        if ($apptId) {
            $appointment = \App\Models\Appointment::with('patient')
                ->where('clinic_id', $clinicId)
                ->find($apptId);

            if ($appointment) {
                $url = route('patients.show', $appointment->patient_id) . "?open_appt={$appointment->id}";
                $results[] = [
                    'type' => 'appointment',
                    'icon' => '<i class="fa-solid fa-calendar-check text-primary"></i>',
                    'title' => "Appointment #{$appointment->id}",
                    'subtitle' => $appointment->patient->full_name . " • " . ucfirst($appointment->status),
                    'meta' => $appointment->scheduled_at->format('d M H:i'),
                    'url' => $url,
                ];
            }
        }

        // ==========================================
        // PRIORITY 2: PATIENT BY ID (Exact Match)
        // Supports: "1024" or "p1024"
        // ==========================================
        $patientId = null;
        if (preg_match('/^p(\d+)$/i', $query, $matches)) {
            $patientId = $matches[1]; // Found 'p' prefix (e.g. p1024 -> 1024)
        } elseif (is_numeric($query)) {
            $patientId = $query;      // Pure number (e.g. 1024)
        }

        $foundPatientById = null;

        if ($patientId) {
            $foundPatientById = \App\Models\Patient::where('clinic_id', $clinicId)->find($patientId);

            if ($foundPatientById) {
                $results[] = [
                    'type' => 'patient',
                    'icon' => '<i class="fa-solid fa-id-badge text-info"></i>', // Distinct Icon for ID match
                    'title' => $foundPatientById->full_name,
                    'subtitle' => "ID: {$foundPatientById->id} • CIN: {$foundPatientById->cin}",
                    'meta' => 'ID Match', // Verified by ID
                    'url' => route('patients.show', $foundPatientById->id),
                ];
            }
        }

        // ==========================================
        // PRIORITY 3: GENERAL PATIENT SEARCH
        // Supports: Name, Phone, and CIN (including "P12345")
        // ==========================================
        if (count($results) < 6) {
            $patients = \App\Models\Patient::where('clinic_id', $clinicId)
                ->where(function ($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%")
                        ->orWhere('cin', 'like', "%{$query}%")  // This naturally handles CINs like 'P12345'
                        ->orWhere('phone', 'like', "%{$query}%");
                })
                // Exclude the patient we already found in Priority 2 (to avoid duplicates)
                ->when($foundPatientById, function ($q) use ($foundPatientById) {
                    return $q->where('id', '!=', $foundPatientById->id);
                })
                ->limit(6 - count($results)) // Fill remaining slots
                ->get();

            foreach ($patients as $patient) {
                $results[] = [
                    'type' => 'patient',
                    'icon' => '<i class="fa-solid fa-user-injured text-success"></i>',
                    'title' => $patient->full_name,
                    'subtitle' => "CIN: {$patient->cin} • {$patient->phone}",
                    'meta' => $patient->age . ' yrs',
                    'url' => route('patients.show', $patient->id),
                ];
            }
        }

        return response()->json($results);
    }


    public function index()
    {
        $user = Auth::user();

        // 1. SUPER ADMIN DASHBOARD
        if ($user->role === 'super_admin') {
            return view('admin.dashboard');
        }

        // 2. DOCTOR / CLINIC DASHBOARD
        if ($user->role === 'doctor') {
            $clinicId = $user->clinic_id;
            $today = Carbon::today();

            // --- A. STATISTICS CARDS ---

            // 1. Waiting Room: Status is 'waiting' today
            $waitingCount = Appointment::where('clinic_id', $clinicId)
                ->whereDate('scheduled_at', $today)
                ->where('status', 'waiting')
                ->count();

            // 2. Today's Total Appointments
            $todayTotal = Appointment::where('clinic_id', $clinicId)
                ->whereDate('scheduled_at', $today)
                ->count();

            // 3. Completed Today
            $todayCompleted = Appointment::where('clinic_id', $clinicId)
                ->whereDate('scheduled_at', $today)
                ->where('status', 'finished')
                ->count();

            // 4. Daily Revenue (Sum of prices for finished appointments today)
            // Note: Ensure your Appointment model has 'price' or use 'paid_amount' if you added that
            $dailyRevenue = Appointment::where('clinic_id', $clinicId)
                ->whereDate('scheduled_at', $today)
                ->where('status', 'finished')
                ->sum('price');

            // --- B. LISTS ---

            // 5. Live Queue (In Consultation + Waiting, ordered by time)
            $queue = Appointment::with('patient')
                ->where('clinic_id', $clinicId)
                ->whereDate('scheduled_at', $today)
                ->whereIn('status', ['in_consultation', 'waiting'])
                ->orderByRaw("FIELD(status, 'in_consultation', 'waiting')") // Put 'In Consultation' first
                ->orderBy('scheduled_at', 'asc')
                ->get();

            // 6. Next Scheduled (Status 'scheduled', ordered by time)
            $nextAppointments = Appointment::with('patient')
                ->where('clinic_id', $clinicId)
                ->whereDate('scheduled_at', $today)
                ->where('status', 'scheduled')
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at', 'asc')
                ->take(5)
                ->get();

            return view('doctor.dashboard', compact(
                'waitingCount',
                'todayTotal',
                'todayCompleted',
                'dailyRevenue',
                'queue',
                'nextAppointments'
            ));
        }

        // 3. SECRETARY DASHBOARD
        if ($user->role === 'secretary') {
            return view('secretary.dashboard');
        }

        return view('dashboard.default');
    }
}