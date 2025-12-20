<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SecretaryController extends Controller
{
    /**
     * Display a list of secretaries belonging to MY clinic.
     */
    public function index()
    {
        // 1. Get the current Doctor's Clinic ID
        $clinicId = Auth::user()->clinic_id;

        // 2. Fetch only 'secretary' roles for THIS clinic
        $secretaries = User::where('clinic_id', $clinicId)
            ->where('role', 'secretary')
            ->latest()
            ->get(); // Using get() since a clinic rarely has > 10 secretaries

        return view('doctor.secretaries', compact('secretaries'));
    }

    /**
     * Store a new Secretary.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        User::create([
            'clinic_id' => Auth::user()->clinic_id, // Force assign to MY clinic
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'secretary', // Force role
            'is_active' => true,
        ]);

        return back()->with('success', 'New Secretary account created!');
    }

    /**
     * Update (Edit) a Secretary.
     */
    public function update(Request $request, $id)
    {
        $secretary = User::where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id) // Security Check
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => $request->has('is_active'),
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $secretary->update($data);

        return back()->with('success', 'Secretary details updated.');
    }

    /**
     * Delete (Fire) a Secretary.
     */
    public function destroy($id)
    {
        $secretary = User::where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->firstOrFail();

        $secretary->delete();

        return back()->with('success', 'Account removed successfully.');
    }
}