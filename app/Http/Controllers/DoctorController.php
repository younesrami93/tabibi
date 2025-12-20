<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        // Fetch only Doctors, with their Clinic info
        $query = User::where('role', 'doctor')->with('clinic');

        // Simple Search Logic
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('clinic', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $doctors = $query->latest()->paginate(10);

        return view('admin.doctors', compact('doctors'));
    }

    // Optional: Manually add a 2nd doctor to an existing clinic


    public function store(Request $request)
    {
        // A. Validate EVERYTHING
        $request->validate([
            // Clinic Info
            'clinic_name' => 'required|string|max:255',
            'clinic_phone' => 'nullable|string',

            // Doctor Info
            'doctor_name' => 'required|string|max:255',
            'doctor_email' => 'required|email|unique:users,email',
            'doctor_password' => 'required|min:6',

            // Subscription
            'plan_type' => 'required|in:monthly,yearly,lifetime',
            'subscription_price' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'expires_at' => 'required|date',

            // Configuration (Optional but good to validate basic format)
            'config.currency_code' => 'nullable|string|size:3',
            'config.slot_duration' => 'nullable|numeric|min:5|max:120',
        ]);

        try {
            DB::transaction(function () use ($request) {

                // 1. Prepare Default Settings
                // Merge form input with any hardcoded system defaults you want
                $defaultConfig = [
                    'language' => 'fr',
                    'notifications_enabled' => true,
                    'theme_color' => 'blue',
                ];

                // Merge: Defaults + User Input from the form
                $finalSettings = array_merge($defaultConfig, $request->input('config', []));

                // 2. Create the Clinic
                $clinic = Clinic::create([
                    'name' => $request->clinic_name,
                    'phone' => $request->clinic_phone,
                    'plan_type' => $request->plan_type,
                    'subscription_price' => $request->subscription_price,
                    'total_paid' => $request->amount_paid,
                    'subscription_expires_at' => $request->expires_at,
                    'is_active' => true,
                    'settings' => $finalSettings, // <--- SAVED AS JSON AUTOMATICALLY
                ]);

                // 3. Create the Doctor
                User::create([
                    'clinic_id' => $clinic->id,
                    'name' => $request->doctor_name,
                    'email' => $request->doctor_email,
                    'password' => Hash::make($request->doctor_password),
                    'role' => 'doctor',
                    'is_active' => true,
                ]);
            });

            return redirect()->route('clinics.index')->with('success', 'Clinic created with custom configuration!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

}