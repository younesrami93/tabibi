<?php

namespace App\Http\Controllers;

use App\Models\MedicalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalServiceController extends Controller
{
    /**
     * List all services for this clinic.
     */
    public function index()
    {
        $services = MedicalService::where('clinic_id', Auth::user()->clinic_id)
            ->latest()
            ->get(); // Using get() because a doctor rarely has > 50 services

        return view('doctor.services', compact('services'));
    }

    /**
     * Create a new service.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        MedicalService::create([
            'clinic_id' => Auth::user()->clinic_id,
            'name' => $request->name,
            'price' => $request->price,
            // New Fields
            'code' => $request->code,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes ?? 30, // Default 30 min
            'is_active' => true,
        ]);

        return back()->with('success', 'Service added successfully.');
    }

    /**
     * Update an existing service.
     */
    public function update(Request $request, $id)
    {
        $service = MedicalService::where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
        ]);

        $service->update([
            'name' => $request->name,
            'price' => $request->price,
            'code' => $request->code,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Service updated.');
    }
    /**
     * Soft delete a service.
     */
    public function destroy($id)
    {
        $service = MedicalService::where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->firstOrFail();

        $service->delete();

        return back()->with('success', 'Service removed from list.');
    }
}