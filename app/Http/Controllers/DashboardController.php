<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();


        if ($user->role === 'super_admin') {
            return view('admin.dashboard');
        }

        if ($user->role === 'doctor') {
            // Doctors need different data (e.g., Today's appointments)
            // $appointments = ...
            return view('doctor.dashboard');
        }

        if ($user->role === 'secretary') {
            return view('secretary.dashboard');
        }

        // Fallback
        return view('dashboard.default');
    }
}