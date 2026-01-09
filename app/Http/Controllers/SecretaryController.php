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
    public function index(Request $request)
    {
        // 1. Base Query: Only secretaries in MY clinic
        $query = User::where('clinic_id', auth()->user()->clinic_id)
            ->where('role', 'secretary');

        // 2. Search Filter (Name or Email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 3. Status Filter (Active vs Inactive)
        if ($request->filled('status') && $request->status !== 'all') {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // 4. Get Results (Pagination)
        $secretaries = $query->latest()->paginate(10)->withQueryString();

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