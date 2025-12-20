<?php


namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClinicController extends Controller
{
    public function index()
    {
        $clinics = Clinic::with('users')->latest()->paginate(2);
        return view('admin.clinics', compact('clinics'));
    }

    public function store(Request $request)
    {
        // Validation ... (Same as before)
        $request->validate([
            'clinic_name' => 'required',
            'doctor_email' => 'required|email|unique:users,email',
            'plan_type' => 'required',
            // ... add other validations if needed
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Prepare Settings
                $settings = [
                    'currency_code' => $request->input('config.currency_code', 'MAD'),
                    'currency_symbol' => $request->input('config.currency_symbol', 'DH'),
                    'country' => $request->input('config.country', 'Morocco'),
                    'calendar_start_time' => $request->input('config.calendar_start_time', '09:00'),
                    'calendar_end_time' => $request->input('config.calendar_end_time', '18:00'),
                    'slot_duration' => $request->input('config.slot_duration', 30),
                ];

                $clinic = Clinic::create([
                    'name' => $request->clinic_name,
                    'phone' => $request->clinic_phone,
                    'plan_type' => $request->plan_type,
                    'subscription_price' => $request->subscription_price,
                    'total_paid' => $request->amount_paid,
                    'subscription_expires_at' => $request->expires_at,
                    'is_active' => true,
                    'settings' => $settings, // <--- SAVES JSON
                ]);

                User::create([
                    'clinic_id' => $clinic->id,
                    'name' => $request->doctor_name,
                    'email' => $request->doctor_email,
                    'password' => Hash::make($request->doctor_password),
                    'role' => 'doctor',
                    'is_active' => true,
                ]);
            });

            return redirect()->back()->with('success', 'Clinic Created Successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    // UPDATE METHOD (FOR EDIT MODAL)
    public function update(Request $request, $id)
    {
        $clinic = Clinic::findOrFail($id);

        try {
            // 1. Update Basic Info
            $clinic->update([
                'name' => $request->clinic_name,
                'phone' => $request->clinic_phone,
                'plan_type' => $request->plan_type,
                'subscription_expires_at' => $request->expires_at,
                // Update Financials (Credit is auto-calculated from these two)
                'subscription_price' => $request->subscription_price,
                'total_paid' => $request->amount_paid,
                'settings' => array_merge($clinic->settings ?? [], $request->input('config', [])),
            ]);

            return back()->with('success', 'Clinic Updated Successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Update Failed: ' . $e->getMessage());
        }
    }
}