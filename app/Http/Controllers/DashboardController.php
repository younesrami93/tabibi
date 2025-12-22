<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
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